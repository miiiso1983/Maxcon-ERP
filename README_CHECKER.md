# 🔍 MAXCON ERP Website Checker

أداة شاملة لفحص جميع روابط موقع MAXCON ERP والتحقق من سلامتها وأدائها.

## ✨ المميزات

### 🎯 الفحص الشامل
- ✅ فحص جميع الروابط تلقائياً
- ✅ استخراج الروابط من HTML و JavaScript
- ✅ دعم الروابط الديناميكية
- ✅ فحص حالة HTTP (200, 404, 500, إلخ)
- ✅ قياس أوقات الاستجابة
- ✅ تتبع إعادة التوجيه

### 🔧 فحص متقدم (مع Selenium)
- ✅ كشف أخطاء JavaScript
- ✅ فحص الموارد المفقودة (CSS/JS)
- ✅ محاكاة تفاعل المستخدم الحقيقي
- ✅ دعم المواقع التفاعلية

### 📊 التقارير المتنوعة
- 📄 تقرير HTML تفاعلي
- 📊 تقرير CSV للتحليل
- 🔧 تقرير JSON للمطورين
- 📝 ملف سجل مفصل

## 🚀 التثبيت والتشغيل

### المتطلبات الأساسية
```bash
# Python 3.7 أو أحدث
python3 --version

# pip لتثبيت المكتبات
pip3 --version
```

### التثبيت السريع
```bash
# تثبيت المكتبات المطلوبة
pip3 install -r requirements.txt

# منح صلاحية التشغيل للسكربت
chmod +x run_checker.sh

# تشغيل الفحص
./run_checker.sh
```

### التشغيل اليدوي

#### 1. الفحص الشامل (مع Selenium)
```bash
python3 website_checker.py
```

#### 2. الفحص المبسط (بدون Selenium)
```bash
python3 simple_checker.py
```

## ⚙️ خيارات التخصيص

### تغيير رابط الموقع
```python
# في بداية السكربت
checker = WebsiteChecker(
    base_url="http://your-domain.com",
    output_dir="./custom_reports"
)
```

### إضافة روابط مخصصة للفحص
```python
# في simple_checker.py
self.essential_routes = [
    '/',
    '/custom-page',
    '/api/endpoint',
    # أضف روابطك هنا
]
```

### استثناء روابط معينة
```python
self.excluded_patterns = [
    r'mailto:',
    r'\.pdf$',
    r'/admin/',  # استثناء صفحات الإدارة
    # أضف أنماط الاستثناء هنا
]
```

## 📊 فهم التقارير

### 🎨 التقرير HTML
- **الملخص العام**: إحصائيات سريعة
- **الروابط المعطلة**: قائمة مفصلة بالأخطاء
- **إعادة التوجيه**: الروابط المحولة
- **الروابط الناجحة**: عينة من الروابط السليمة

### 📈 التقرير CSV
مناسب للتحليل في Excel أو Google Sheets:
```csv
url,status_code,response_time,error,content_type,redirected
http://localhost:8000,200,0.156,,text/html,false
http://localhost:8000/404,404,0.089,Not Found,text/html,false
```

### 🔧 التقرير JSON
للمطورين والتكامل مع أدوات أخرى:
```json
{
  "metadata": {
    "timestamp": "2025-07-03T20:30:00",
    "base_url": "http://localhost:8000",
    "total_checked": 45
  },
  "summary": {
    "successful": 42,
    "errors": 3,
    "success_rate": 93.3
  }
}
```

## 🔧 استكشاف الأخطاء

### مشاكل شائعة وحلولها

#### 1. خطأ "Chrome not found"
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install google-chrome-stable

# macOS
brew install --cask google-chrome

# أو استخدم الفحص المبسط
python3 simple_checker.py
```

#### 2. خطأ "Permission denied"
```bash
chmod +x run_checker.sh
chmod +x website_checker.py
chmod +x simple_checker.py
```

#### 3. خطأ "Module not found"
```bash
pip3 install -r requirements.txt
# أو
pip3 install selenium requests beautifulsoup4 lxml
```

#### 4. بطء في الفحص
```python
# قلل من التأخير في السكربت
time.sleep(0.1)  # بدلاً من 0.5

# أو قلل عدد الروابط المفحوصة
self.essential_routes = self.essential_routes[:10]
```

## 📋 أمثلة للاستخدام

### فحص موقع محلي
```bash
python3 simple_checker.py
```

### فحص موقع على خادم
```python
checker = SimpleWebsiteChecker(
    base_url="https://your-production-site.com"
)
checker.run()
```

### فحص مجدول (Cron Job)
```bash
# إضافة إلى crontab للفحص اليومي
0 2 * * * cd /path/to/maxcon-erp && python3 simple_checker.py
```

### فحص مع إشعارات
```python
# إضافة إشعار عند وجود أخطاء
if len(checker.errors) > 0:
    send_email_notification(checker.errors)
```

## 🎯 نصائح للاستخدام الأمثل

### 1. الفحص المنتظم
- اجعل الفحص جزءاً من عملية النشر
- استخدم الفحص المجدول للمراقبة المستمرة

### 2. تحليل النتائج
- راجع التقارير بانتظام
- اهتم بأوقات الاستجابة البطيئة
- تابع الروابط المعاد توجيهها

### 3. التحسين
- أصلح الروابط المعطلة فوراً
- حسن أداء الصفحات البطيئة
- راجع أخطاء JavaScript

## 🔄 التحديثات والصيانة

### تحديث المكتبات
```bash
pip3 install --upgrade -r requirements.txt
```

### إضافة ميزات جديدة
- فحص أداء الصور
- اختبار الأمان الأساسي
- فحص SEO
- تحليل إمكانية الوصول

## 📞 الدعم والمساعدة

### الأخطاء الشائعة
1. **Timeout errors**: زيادة قيمة timeout
2. **Memory errors**: تقليل عدد الروابط المفحوصة
3. **SSL errors**: إضافة verify=False للاختبار

### تحسين الأداء
- استخدم threading للفحص المتوازي
- قم بتخزين النتائج مؤقتاً
- استخدم connection pooling

---

## 📄 الملفات المتضمنة

- `website_checker.py` - الفحص الشامل مع Selenium
- `simple_checker.py` - الفحص المبسط بدون Selenium  
- `run_checker.sh` - سكربت التشغيل التلقائي
- `requirements.txt` - المكتبات المطلوبة
- `README_CHECKER.md` - هذا الدليل

## 🏆 النتائج المتوقعة

بعد تشغيل الفحص، ستحصل على:
- ✅ تأكيد سلامة جميع الروابط الأساسية
- 📊 تقرير مفصل بالأداء
- 🔧 قائمة بالمشاكل التي تحتاج إصلاح
- 📈 مقاييس الأداء والاستجابة

**نتيجة مثالية**: 95%+ من الروابط تعمل بنجاح مع أوقات استجابة أقل من ثانيتين.
