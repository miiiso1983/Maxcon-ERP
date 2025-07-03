#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
MAXCON ERP Website Link Checker
تحقق شامل من جميع روابط الموقع وتسجيل الأخطاء
"""

import requests
import time
import csv
import json
import logging
from datetime import datetime
from urllib.parse import urljoin, urlparse, parse_qs
from selenium import webdriver
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException, WebDriverException
from bs4 import BeautifulSoup
import re

class WebsiteChecker:
    def __init__(self, base_url="http://localhost:8000", output_dir="./reports"):
        self.base_url = base_url
        self.output_dir = output_dir
        self.session = requests.Session()
        self.checked_urls = set()
        self.errors = []
        self.js_errors = []
        self.resource_errors = []
        self.successful_links = []
        
        # إعداد التسجيل
        self.setup_logging()
        
        # إعداد Selenium
        self.setup_selenium()
        
        # قائمة الروابط المستثناة
        self.excluded_patterns = [
            r'mailto:',
            r'tel:',
            r'javascript:',
            r'#',
            r'\.pdf$',
            r'\.zip$',
            r'\.exe$'
        ]
        
        # الروابط الديناميكية للاختبار
        self.dynamic_routes = [
            '/customers/1',
            '/products/1', 
            '/sales/1',
            '/medical-reps/reps/1',
            '/whatsapp/messages/1',
            '/whatsapp/templates/1',
            '?lang=ar',
            '?lang=en',
            '?lang=ku'
        ]

    def setup_logging(self):
        """إعداد نظام التسجيل"""
        logging.basicConfig(
            level=logging.INFO,
            format='%(asctime)s - %(levelname)s - %(message)s',
            handlers=[
                logging.FileHandler(f'{self.output_dir}/checker.log', encoding='utf-8'),
                logging.StreamHandler()
            ]
        )
        self.logger = logging.getLogger(__name__)

    def setup_selenium(self):
        """إعداد متصفح Selenium"""
        chrome_options = Options()
        chrome_options.add_argument('--headless')
        chrome_options.add_argument('--no-sandbox')
        chrome_options.add_argument('--disable-dev-shm-usage')
        chrome_options.add_argument('--disable-gpu')
        chrome_options.add_argument('--window-size=1920,1080')
        
        # تمكين تسجيل أخطاء JavaScript
        chrome_options.add_argument('--enable-logging')
        chrome_options.add_argument('--log-level=0')
        
        try:
            self.driver = webdriver.Chrome(options=chrome_options)
            self.driver.set_page_load_timeout(30)
        except Exception as e:
            self.logger.error(f"فشل في إعداد Selenium: {e}")
            self.driver = None

    def is_excluded_url(self, url):
        """التحقق من استثناء الرابط"""
        for pattern in self.excluded_patterns:
            if re.search(pattern, url, re.IGNORECASE):
                return True
        return False

    def normalize_url(self, url):
        """تطبيع الرابط"""
        if url.startswith('//'):
            url = 'http:' + url
        elif url.startswith('/'):
            url = urljoin(self.base_url, url)
        elif not url.startswith(('http://', 'https://')):
            url = urljoin(self.base_url, url)
        
        # إزالة المعاملات غير المهمة
        parsed = urlparse(url)
        if parsed.netloc != urlparse(self.base_url).netloc:
            return None
            
        return url

    def check_http_status(self, url):
        """التحقق من حالة HTTP للرابط"""
        try:
            response = self.session.get(url, timeout=10, allow_redirects=True)
            return {
                'url': url,
                'status_code': response.status_code,
                'response_time': response.elapsed.total_seconds(),
                'final_url': response.url,
                'error': None
            }
        except requests.exceptions.RequestException as e:
            return {
                'url': url,
                'status_code': None,
                'response_time': None,
                'final_url': None,
                'error': str(e)
            }

    def extract_links_from_page(self, url):
        """استخراج جميع الروابط من الصفحة"""
        links = set()
        
        try:
            response = self.session.get(url, timeout=10)
            if response.status_code == 200:
                soup = BeautifulSoup(response.content, 'html.parser')
                
                # استخراج روابط <a>
                for link in soup.find_all('a', href=True):
                    href = link['href']
                    normalized = self.normalize_url(href)
                    if normalized and not self.is_excluded_url(normalized):
                        links.add(normalized)
                
                # استخراج روابط من JavaScript (route() calls)
                scripts = soup.find_all('script')
                for script in scripts:
                    if script.string:
                        route_matches = re.findall(r"route\(['\"]([^'\"]+)['\"]", script.string)
                        for route in route_matches:
                            normalized = self.normalize_url('/' + route.replace('.', '/'))
                            if normalized:
                                links.add(normalized)
                
        except Exception as e:
            self.logger.error(f"خطأ في استخراج الروابط من {url}: {e}")
        
        return links

    def check_javascript_errors(self, url):
        """التحقق من أخطاء JavaScript"""
        if not self.driver:
            return []
        
        js_errors = []
        try:
            self.driver.get(url)
            
            # انتظار تحميل الصفحة
            WebDriverWait(self.driver, 10).until(
                EC.presence_of_element_located((By.TAG_NAME, "body"))
            )
            
            # التحقق من أخطاء JavaScript في console
            logs = self.driver.get_log('browser')
            for log in logs:
                if log['level'] in ['SEVERE', 'ERROR']:
                    js_errors.append({
                        'url': url,
                        'level': log['level'],
                        'message': log['message'],
                        'timestamp': log['timestamp']
                    })
            
            # التحقق من الموارد المفقودة
            failed_resources = self.driver.execute_script("""
                var resources = performance.getEntriesByType('resource');
                var failed = [];
                resources.forEach(function(resource) {
                    if (resource.transferSize === 0 && resource.decodedBodySize === 0) {
                        failed.push({
                            name: resource.name,
                            type: resource.initiatorType,
                            duration: resource.duration
                        });
                    }
                });
                return failed;
            """)
            
            for resource in failed_resources:
                self.resource_errors.append({
                    'url': url,
                    'resource_url': resource['name'],
                    'resource_type': resource['type'],
                    'error': 'فشل في تحميل المورد'
                })
                
        except TimeoutException:
            js_errors.append({
                'url': url,
                'level': 'ERROR',
                'message': 'انتهت مهلة تحميل الصفحة',
                'timestamp': int(time.time() * 1000)
            })
        except WebDriverException as e:
            js_errors.append({
                'url': url,
                'level': 'ERROR', 
                'message': f'خطأ في WebDriver: {str(e)}',
                'timestamp': int(time.time() * 1000)
            })
        
        return js_errors

    def check_single_url(self, url):
        """فحص رابط واحد بشكل شامل"""
        if url in self.checked_urls:
            return
        
        self.checked_urls.add(url)
        self.logger.info(f"فحص الرابط: {url}")
        
        # فحص حالة HTTP
        http_result = self.check_http_status(url)
        
        # فحص أخطاء JavaScript
        js_errors = self.check_javascript_errors(url)
        
        # تسجيل النتائج
        if http_result['status_code'] == 200:
            self.successful_links.append(http_result)
            self.logger.info(f"✅ نجح: {url} ({http_result['response_time']:.2f}s)")
        else:
            self.errors.append(http_result)
            self.logger.error(f"❌ فشل: {url} - {http_result['status_code']} - {http_result['error']}")
        
        # تسجيل أخطاء JavaScript
        if js_errors:
            self.js_errors.extend(js_errors)
            for error in js_errors:
                self.logger.warning(f"⚠️ خطأ JS في {url}: {error['message']}")

    def crawl_website(self):
        """الزحف إلى الموقع واستخراج جميع الروابط"""
        self.logger.info("بدء الزحف إلى الموقع...")
        
        # البدء من الصفحة الرئيسية
        urls_to_check = {self.base_url}
        
        # إضافة الروابط الديناميكية
        for route in self.dynamic_routes:
            dynamic_url = urljoin(self.base_url, route)
            urls_to_check.add(dynamic_url)
        
        # استخراج الروابط من الصفحة الرئيسية
        main_page_links = self.extract_links_from_page(self.base_url)
        urls_to_check.update(main_page_links)
        
        # فحص كل رابط
        for url in urls_to_check:
            self.check_single_url(url)
            time.sleep(0.5)  # تأخير قصير لتجنب إرهاق الخادم

    def generate_reports(self):
        """إنشاء التقارير"""
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        
        # تقرير CSV للأخطاء
        if self.errors:
            csv_file = f"{self.output_dir}/errors_{timestamp}.csv"
            with open(csv_file, 'w', newline='', encoding='utf-8') as f:
                writer = csv.DictWriter(f, fieldnames=['url', 'status_code', 'error', 'response_time'])
                writer.writeheader()
                writer.writerows(self.errors)
            self.logger.info(f"تم إنشاء تقرير الأخطاء: {csv_file}")
        
        # تقرير HTML شامل
        html_file = f"{self.output_dir}/report_{timestamp}.html"
        self.generate_html_report(html_file)
        
        # تقرير JSON
        json_file = f"{self.output_dir}/report_{timestamp}.json"
        self.generate_json_report(json_file)

    def generate_html_report(self, filename):
        """إنشاء تقرير HTML"""
        html_content = f"""
        <!DOCTYPE html>
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>تقرير فحص موقع MAXCON ERP</title>
            <style>
                body {{ font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; }}
                .header {{ background: #007bff; color: white; padding: 20px; border-radius: 5px; }}
                .summary {{ display: flex; gap: 20px; margin: 20px 0; }}
                .card {{ background: #f8f9fa; padding: 15px; border-radius: 5px; flex: 1; }}
                .success {{ border-left: 4px solid #28a745; }}
                .error {{ border-left: 4px solid #dc3545; }}
                .warning {{ border-left: 4px solid #ffc107; }}
                table {{ width: 100%; border-collapse: collapse; margin: 20px 0; }}
                th, td {{ padding: 10px; text-align: right; border: 1px solid #ddd; }}
                th {{ background: #f8f9fa; }}
                .status-200 {{ color: #28a745; }}
                .status-error {{ color: #dc3545; }}
            </style>
        </head>
        <body>
            <div class="header">
                <h1>تقرير فحص موقع MAXCON ERP</h1>
                <p>تاريخ الفحص: {datetime.now().strftime("%Y-%m-%d %H:%M:%S")}</p>
            </div>
            
            <div class="summary">
                <div class="card success">
                    <h3>الروابط الناجحة</h3>
                    <h2>{len(self.successful_links)}</h2>
                </div>
                <div class="card error">
                    <h3>الروابط المعطلة</h3>
                    <h2>{len(self.errors)}</h2>
                </div>
                <div class="card warning">
                    <h3>أخطاء JavaScript</h3>
                    <h2>{len(self.js_errors)}</h2>
                </div>
                <div class="card warning">
                    <h3>الموارد المفقودة</h3>
                    <h2>{len(self.resource_errors)}</h2>
                </div>
            </div>
        """
        
        # إضافة جدول الأخطاء
        if self.errors:
            html_content += """
            <h2>الروابط المعطلة</h2>
            <table>
                <tr><th>الرابط</th><th>رمز الحالة</th><th>الخطأ</th><th>وقت الاستجابة</th></tr>
            """
            for error in self.errors:
                html_content += f"""
                <tr>
                    <td>{error['url']}</td>
                    <td class="status-error">{error['status_code'] or 'N/A'}</td>
                    <td>{error['error'] or 'N/A'}</td>
                    <td>{error['response_time'] or 'N/A'}</td>
                </tr>
                """
            html_content += "</table>"
        
        # إضافة أخطاء JavaScript
        if self.js_errors:
            html_content += """
            <h2>أخطاء JavaScript</h2>
            <table>
                <tr><th>الصفحة</th><th>المستوى</th><th>رسالة الخطأ</th></tr>
            """
            for error in self.js_errors:
                html_content += f"""
                <tr>
                    <td>{error['url']}</td>
                    <td>{error['level']}</td>
                    <td>{error['message']}</td>
                </tr>
                """
            html_content += "</table>"
        
        html_content += "</body></html>"
        
        with open(filename, 'w', encoding='utf-8') as f:
            f.write(html_content)
        
        self.logger.info(f"تم إنشاء تقرير HTML: {filename}")

    def generate_json_report(self, filename):
        """إنشاء تقرير JSON"""
        report_data = {
            'timestamp': datetime.now().isoformat(),
            'base_url': self.base_url,
            'summary': {
                'total_checked': len(self.checked_urls),
                'successful': len(self.successful_links),
                'errors': len(self.errors),
                'js_errors': len(self.js_errors),
                'resource_errors': len(self.resource_errors)
            },
            'successful_links': self.successful_links,
            'errors': self.errors,
            'js_errors': self.js_errors,
            'resource_errors': self.resource_errors
        }
        
        with open(filename, 'w', encoding='utf-8') as f:
            json.dump(report_data, f, ensure_ascii=False, indent=2)
        
        self.logger.info(f"تم إنشاء تقرير JSON: {filename}")

    def run(self):
        """تشغيل الفحص الشامل"""
        try:
            self.logger.info("بدء فحص الموقع...")
            start_time = time.time()
            
            # إنشاء مجلد التقارير
            import os
            os.makedirs(self.output_dir, exist_ok=True)
            
            # الزحف وفحص الموقع
            self.crawl_website()
            
            # إنشاء التقارير
            self.generate_reports()
            
            # طباعة الملخص
            duration = time.time() - start_time
            self.logger.info(f"""
            ==================== ملخص الفحص ====================
            إجمالي الروابط المفحوصة: {len(self.checked_urls)}
            الروابط الناجحة: {len(self.successful_links)}
            الروابط المعطلة: {len(self.errors)}
            أخطاء JavaScript: {len(self.js_errors)}
            الموارد المفقودة: {len(self.resource_errors)}
            مدة الفحص: {duration:.2f} ثانية
            ====================================================
            """)
            
        except Exception as e:
            self.logger.error(f"خطأ في تشغيل الفحص: {e}")
        finally:
            if self.driver:
                self.driver.quit()

if __name__ == "__main__":
    # تشغيل الفحص
    checker = WebsiteChecker()
    checker.run()
