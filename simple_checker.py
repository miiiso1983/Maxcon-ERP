#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
MAXCON ERP Simple Website Link Checker
فحص مبسط لروابط الموقع بدون Selenium
"""

import requests
import time
import csv
import json
import logging
from datetime import datetime
from urllib.parse import urljoin, urlparse
from bs4 import BeautifulSoup
import re
import os

class SimpleWebsiteChecker:
    def __init__(self, base_url="http://localhost:8000", output_dir="./reports"):
        self.base_url = base_url
        self.output_dir = output_dir
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'MAXCON-ERP-Checker/1.0'
        })
        
        self.checked_urls = set()
        self.errors = []
        self.successful_links = []
        self.redirects = []
        
        # إعداد التسجيل
        self.setup_logging()
        
        # الروابط المستثناة
        self.excluded_patterns = [
            r'mailto:', r'tel:', r'javascript:', r'#$',
            r'\.pdf$', r'\.zip$', r'\.exe$', r'\.doc$'
        ]
        
        # الروابط الأساسية للفحص
        self.essential_routes = [
            '/',
            '/dashboard',
            '/inventory',
            '/inventory/products',
            '/inventory/categories',
            '/inventory/warehouses',
            '/sales',
            '/sales/create',
            '/sales/pos',
            '/customers',
            '/customers/create',
            '/customers/import',
            '/suppliers',
            '/suppliers/import',
            '/financial',
            '/financial/collections',
            '/financial/accounting',
            '/reports',
            '/reports/sales',
            '/reports/customers',
            '/ai',
            '/ai/customer-analytics',
            '/ai/demand-forecasting',
            '/hr',
            '/hr/employees',
            '/hr/attendance',
            '/medical-reps',
            '/medical-reps/reps',
            '/medical-reps/visits',
            '/medical-reps/performance',
            '/compliance',
            '/compliance/items',
            '/compliance/inspections',
            '/testing',
            '/testing/modules',
            '/testing/results',
            '/analytics',
            '/analytics/sales',
            '/analytics/customers',
            '/performance',
            '/performance/monitoring',
            '/performance/cache',
            '/whatsapp',
            '/whatsapp/messages',
            '/whatsapp/templates',
            '/whatsapp/settings',
            '/purchase-orders'
        ]

    def setup_logging(self):
        """إعداد نظام التسجيل"""
        os.makedirs(self.output_dir, exist_ok=True)
        
        logging.basicConfig(
            level=logging.INFO,
            format='%(asctime)s - %(levelname)s - %(message)s',
            handlers=[
                logging.FileHandler(f'{self.output_dir}/simple_checker.log', encoding='utf-8'),
                logging.StreamHandler()
            ]
        )
        self.logger = logging.getLogger(__name__)

    def is_excluded_url(self, url):
        """التحقق من استثناء الرابط"""
        for pattern in self.excluded_patterns:
            if re.search(pattern, url, re.IGNORECASE):
                return True
        return False

    def normalize_url(self, url):
        """تطبيع الرابط"""
        if not url:
            return None
            
        # إزالة المسافات والأحرف الخاصة
        url = url.strip()
        
        if url.startswith('//'):
            url = 'http:' + url
        elif url.startswith('/'):
            url = urljoin(self.base_url, url)
        elif not url.startswith(('http://', 'https://')):
            url = urljoin(self.base_url, url)
        
        # التحقق من أن الرابط ينتمي لنفس النطاق
        parsed = urlparse(url)
        base_parsed = urlparse(self.base_url)
        
        if parsed.netloc and parsed.netloc != base_parsed.netloc:
            return None
            
        return url

    def check_url_status(self, url):
        """فحص حالة الرابط"""
        try:
            start_time = time.time()
            response = self.session.get(url, timeout=15, allow_redirects=True)
            response_time = time.time() - start_time
            
            result = {
                'url': url,
                'status_code': response.status_code,
                'response_time': round(response_time, 3),
                'final_url': response.url,
                'content_type': response.headers.get('content-type', ''),
                'content_length': len(response.content),
                'error': None,
                'redirected': url != response.url
            }
            
            # تسجيل إعادة التوجيه
            if result['redirected']:
                self.redirects.append({
                    'original_url': url,
                    'final_url': response.url,
                    'status_code': response.status_code
                })
            
            return result
            
        except requests.exceptions.Timeout:
            return {
                'url': url, 'status_code': None, 'response_time': None,
                'final_url': None, 'content_type': '', 'content_length': 0,
                'error': 'انتهت مهلة الاتصال (Timeout)', 'redirected': False
            }
        except requests.exceptions.ConnectionError:
            return {
                'url': url, 'status_code': None, 'response_time': None,
                'final_url': None, 'content_type': '', 'content_length': 0,
                'error': 'فشل في الاتصال (Connection Error)', 'redirected': False
            }
        except requests.exceptions.RequestException as e:
            return {
                'url': url, 'status_code': None, 'response_time': None,
                'final_url': None, 'content_type': '', 'content_length': 0,
                'error': f'خطأ في الطلب: {str(e)}', 'redirected': False
            }

    def extract_links_from_html(self, url, html_content):
        """استخراج الروابط من محتوى HTML"""
        links = set()
        
        try:
            soup = BeautifulSoup(html_content, 'html.parser')
            
            # استخراج روابط <a>
            for link in soup.find_all('a', href=True):
                href = link['href']
                normalized = self.normalize_url(href)
                if normalized and not self.is_excluded_url(normalized):
                    links.add(normalized)
            
            # استخراج روابط من النماذج
            for form in soup.find_all('form', action=True):
                action = form['action']
                normalized = self.normalize_url(action)
                if normalized and not self.is_excluded_url(normalized):
                    links.add(normalized)
            
            # البحث عن route() في JavaScript
            scripts = soup.find_all('script')
            for script in scripts:
                if script.string:
                    # البحث عن route('route.name')
                    route_matches = re.findall(r"route\(['\"]([^'\"]+)['\"]", script.string)
                    for route in route_matches:
                        # تحويل route.name إلى URL
                        route_url = '/' + route.replace('.', '/')
                        normalized = self.normalize_url(route_url)
                        if normalized:
                            links.add(normalized)
                    
                    # البحث عن URLs مباشرة
                    url_matches = re.findall(r'["\']([/][^"\']*)["\']', script.string)
                    for url_match in url_matches:
                        if len(url_match) > 1:  # تجنب الروابط القصيرة جداً
                            normalized = self.normalize_url(url_match)
                            if normalized and not self.is_excluded_url(normalized):
                                links.add(normalized)
        
        except Exception as e:
            self.logger.warning(f"خطأ في استخراج الروابط من {url}: {e}")
        
        return links

    def check_single_url(self, url):
        """فحص رابط واحد"""
        if url in self.checked_urls:
            return None
        
        self.checked_urls.add(url)
        self.logger.info(f"فحص: {url}")
        
        result = self.check_url_status(url)
        
        # تصنيف النتيجة
        if result['status_code'] == 200:
            self.successful_links.append(result)
            self.logger.info(f"✅ {url} - {result['response_time']}s")
        elif result['status_code'] in [301, 302, 303, 307, 308]:
            self.successful_links.append(result)
            self.logger.info(f"↗️  {url} -> {result['final_url']} ({result['status_code']})")
        else:
            self.errors.append(result)
            error_msg = result['error'] or f"HTTP {result['status_code']}"
            self.logger.error(f"❌ {url} - {error_msg}")
        
        return result

    def crawl_and_check(self):
        """الزحف وفحص جميع الروابط"""
        self.logger.info("بدء فحص الموقع...")
        
        # فحص الروابط الأساسية أولاً
        all_urls = set()
        for route in self.essential_routes:
            url = urljoin(self.base_url, route)
            all_urls.add(url)
        
        # فحص الصفحة الرئيسية واستخراج الروابط منها
        try:
            main_result = self.check_single_url(self.base_url)
            if main_result and main_result['status_code'] == 200:
                response = self.session.get(self.base_url)
                extracted_links = self.extract_links_from_html(self.base_url, response.content)
                all_urls.update(extracted_links)
        except Exception as e:
            self.logger.error(f"خطأ في فحص الصفحة الرئيسية: {e}")
        
        # فحص جميع الروابط
        total_urls = len(all_urls)
        for i, url in enumerate(all_urls, 1):
            self.logger.info(f"التقدم: {i}/{total_urls}")
            self.check_single_url(url)
            time.sleep(0.2)  # تأخير قصير

    def generate_reports(self):
        """إنشاء التقارير"""
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        
        # تقرير CSV
        self.generate_csv_report(f"{self.output_dir}/report_{timestamp}.csv")
        
        # تقرير HTML
        self.generate_html_report(f"{self.output_dir}/report_{timestamp}.html")
        
        # تقرير JSON
        self.generate_json_report(f"{self.output_dir}/report_{timestamp}.json")

    def generate_csv_report(self, filename):
        """إنشاء تقرير CSV"""
        with open(filename, 'w', newline='', encoding='utf-8') as f:
            fieldnames = ['url', 'status_code', 'response_time', 'error', 'content_type', 'redirected']
            writer = csv.DictWriter(f, fieldnames=fieldnames)
            writer.writeheader()
            
            # كتابة الروابط الناجحة
            for link in self.successful_links:
                writer.writerow({
                    'url': link['url'],
                    'status_code': link['status_code'],
                    'response_time': link['response_time'],
                    'error': '',
                    'content_type': link['content_type'],
                    'redirected': link['redirected']
                })
            
            # كتابة الأخطاء
            for error in self.errors:
                writer.writerow({
                    'url': error['url'],
                    'status_code': error['status_code'],
                    'response_time': error['response_time'],
                    'error': error['error'],
                    'content_type': error['content_type'],
                    'redirected': error['redirected']
                })
        
        self.logger.info(f"تم إنشاء تقرير CSV: {filename}")

    def generate_html_report(self, filename):
        """إنشاء تقرير HTML مفصل"""
        html_content = f"""
        <!DOCTYPE html>
        <html dir="rtl" lang="ar">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>تقرير فحص موقع MAXCON ERP</title>
            <style>
                body {{ font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; background: #f5f5f5; }}
                .container {{ max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }}
                .header {{ background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 30px; border-radius: 10px; text-align: center; }}
                .summary {{ display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 30px 0; }}
                .card {{ background: #f8f9fa; padding: 20px; border-radius: 10px; text-align: center; border-left: 5px solid #007bff; }}
                .card.success {{ border-left-color: #28a745; }}
                .card.error {{ border-left-color: #dc3545; }}
                .card.warning {{ border-left-color: #ffc107; }}
                .card h3 {{ margin: 0 0 10px 0; color: #333; }}
                .card .number {{ font-size: 2em; font-weight: bold; color: #007bff; }}
                table {{ width: 100%; border-collapse: collapse; margin: 20px 0; }}
                th, td {{ padding: 12px; text-align: right; border: 1px solid #ddd; }}
                th {{ background: #f8f9fa; font-weight: bold; }}
                .status-200 {{ color: #28a745; font-weight: bold; }}
                .status-redirect {{ color: #17a2b8; font-weight: bold; }}
                .status-error {{ color: #dc3545; font-weight: bold; }}
                .url {{ word-break: break-all; }}
                .section {{ margin: 30px 0; }}
                .section h2 {{ color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }}
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🔍 تقرير فحص موقع MAXCON ERP</h1>
                    <p>تاريخ الفحص: {datetime.now().strftime("%Y-%m-%d %H:%M:%S")}</p>
                    <p>الموقع المفحوص: {self.base_url}</p>
                </div>
                
                <div class="summary">
                    <div class="card success">
                        <h3>إجمالي الروابط</h3>
                        <div class="number">{len(self.checked_urls)}</div>
                    </div>
                    <div class="card success">
                        <h3>الروابط الناجحة</h3>
                        <div class="number">{len(self.successful_links)}</div>
                    </div>
                    <div class="card error">
                        <h3>الروابط المعطلة</h3>
                        <div class="number">{len(self.errors)}</div>
                    </div>
                    <div class="card warning">
                        <h3>إعادة التوجيه</h3>
                        <div class="number">{len(self.redirects)}</div>
                    </div>
                </div>
        """
        
        # إضافة جدول الأخطاء
        if self.errors:
            html_content += """
                <div class="section">
                    <h2>❌ الروابط المعطلة</h2>
                    <table>
                        <tr><th>الرابط</th><th>رمز الحالة</th><th>الخطأ</th><th>وقت الاستجابة</th></tr>
            """
            for error in self.errors:
                status_class = "status-error"
                html_content += f"""
                        <tr>
                            <td class="url">{error['url']}</td>
                            <td class="{status_class}">{error['status_code'] or 'N/A'}</td>
                            <td>{error['error'] or 'غير محدد'}</td>
                            <td>{error['response_time'] or 'N/A'}</td>
                        </tr>
                """
            html_content += "</table></div>"
        
        # إضافة جدول إعادة التوجيه
        if self.redirects:
            html_content += """
                <div class="section">
                    <h2>↗️ إعادة التوجيه</h2>
                    <table>
                        <tr><th>الرابط الأصلي</th><th>الرابط النهائي</th><th>رمز الحالة</th></tr>
            """
            for redirect in self.redirects:
                html_content += f"""
                        <tr>
                            <td class="url">{redirect['original_url']}</td>
                            <td class="url">{redirect['final_url']}</td>
                            <td class="status-redirect">{redirect['status_code']}</td>
                        </tr>
                """
            html_content += "</table></div>"
        
        # إضافة جدول الروابط الناجحة (عينة)
        if self.successful_links:
            html_content += """
                <div class="section">
                    <h2>✅ الروابط الناجحة (عينة)</h2>
                    <table>
                        <tr><th>الرابط</th><th>رمز الحالة</th><th>وقت الاستجابة</th><th>نوع المحتوى</th></tr>
            """
            # عرض أول 20 رابط ناجح
            for link in self.successful_links[:20]:
                status_class = "status-200" if link['status_code'] == 200 else "status-redirect"
                html_content += f"""
                        <tr>
                            <td class="url">{link['url']}</td>
                            <td class="{status_class}">{link['status_code']}</td>
                            <td>{link['response_time']}s</td>
                            <td>{link['content_type'][:50]}...</td>
                        </tr>
                """
            html_content += "</table></div>"
        
        html_content += """
                <div class="section">
                    <p style="text-align: center; color: #666; margin-top: 40px;">
                        تم إنشاء هذا التقرير بواسطة MAXCON ERP Website Checker
                    </p>
                </div>
            </div>
        </body>
        </html>
        """
        
        with open(filename, 'w', encoding='utf-8') as f:
            f.write(html_content)
        
        self.logger.info(f"تم إنشاء تقرير HTML: {filename}")

    def generate_json_report(self, filename):
        """إنشاء تقرير JSON"""
        report_data = {
            'metadata': {
                'timestamp': datetime.now().isoformat(),
                'base_url': self.base_url,
                'checker_version': '1.0',
                'total_checked': len(self.checked_urls)
            },
            'summary': {
                'successful': len(self.successful_links),
                'errors': len(self.errors),
                'redirects': len(self.redirects),
                'success_rate': round((len(self.successful_links) / len(self.checked_urls)) * 100, 2) if self.checked_urls else 0
            },
            'results': {
                'successful_links': self.successful_links,
                'errors': self.errors,
                'redirects': self.redirects
            }
        }
        
        with open(filename, 'w', encoding='utf-8') as f:
            json.dump(report_data, f, ensure_ascii=False, indent=2)
        
        self.logger.info(f"تم إنشاء تقرير JSON: {filename}")

    def run(self):
        """تشغيل الفحص"""
        try:
            start_time = time.time()
            self.logger.info("🚀 بدء فحص موقع MAXCON ERP...")
            
            # الزحف والفحص
            self.crawl_and_check()
            
            # إنشاء التقارير
            self.generate_reports()
            
            # طباعة الملخص النهائي
            duration = time.time() - start_time
            success_rate = (len(self.successful_links) / len(self.checked_urls)) * 100 if self.checked_urls else 0
            
            print(f"""
╔══════════════════════════════════════════════════════════════╗
║                    📊 ملخص فحص الموقع                      ║
╠══════════════════════════════════════════════════════════════╣
║ إجمالي الروابط المفحوصة: {len(self.checked_urls):>30} ║
║ الروابط الناجحة: {len(self.successful_links):>37} ║
║ الروابط المعطلة: {len(self.errors):>38} ║
║ إعادة التوجيه: {len(self.redirects):>40} ║
║ معدل النجاح: {success_rate:>6.1f}%                           ║
║ مدة الفحص: {duration:>6.1f} ثانية                          ║
╚══════════════════════════════════════════════════════════════╝
            """)
            
            if self.errors:
                print("\n❌ الروابط المعطلة:")
                for error in self.errors[:10]:  # عرض أول 10 أخطاء
                    print(f"   • {error['url']} - {error['error'] or error['status_code']}")
                if len(self.errors) > 10:
                    print(f"   ... و {len(self.errors) - 10} أخطاء أخرى")
            
            print(f"\n📁 التقارير محفوظة في: {self.output_dir}/")
            
        except KeyboardInterrupt:
            self.logger.info("تم إيقاف الفحص بواسطة المستخدم")
        except Exception as e:
            self.logger.error(f"خطأ في تشغيل الفحص: {e}")

if __name__ == "__main__":
    checker = SimpleWebsiteChecker()
    checker.run()
