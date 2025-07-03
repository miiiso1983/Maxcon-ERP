#!/bin/bash

# سكربت تشغيل فحص الموقع
# MAXCON ERP Website Checker Runner

echo "🚀 أداة فحص موقع MAXCON ERP"
echo "=================================="
echo "اختر نوع الفحص:"
echo "1) فحص سريع (15 رابط أساسي)"
echo "2) فحص شامل بدون JavaScript"
echo "3) فحص شامل مع JavaScript (يتطلب Chrome)"
echo "=================================="
read -p "اختر الرقم (1-3): " choice

# التحقق من وجود Python
if ! command -v python3 &> /dev/null; then
    echo "❌ Python3 غير مثبت. يرجى تثبيت Python3 أولاً."
    exit 1
fi

# إنشاء مجلد التقارير
mkdir -p reports

case $choice in
    1)
        echo "🏃‍♂️ تشغيل الفحص السريع..."
        python3 quick_check.py
        ;;
    2)
        echo "📦 التحقق من المكتبات المطلوبة..."
        if ! python3 -c "import requests, beautifulsoup4" 2>/dev/null; then
            echo "📦 تثبيت المكتبات المطلوبة..."
            pip3 install requests beautifulsoup4 lxml
        fi

        echo "🔍 تشغيل الفحص الشامل..."
        python3 simple_checker.py

        echo ""
        echo "✅ انتهى الفحص! تحقق من مجلد reports للحصول على التقارير."
        echo ""
        echo "📊 التقارير المتاحة:"
        ls -la reports/ | grep -E "\.(html|csv|json|log)$" | tail -5

        echo ""
        echo "🌐 لعرض التقرير في المتصفح:"
        latest_report=$(ls -t reports/report_*.html 2>/dev/null | head -1)
        if [ -n "$latest_report" ]; then
            echo "open $latest_report"
            # فتح التقرير تلقائياً على macOS
            if command -v open &> /dev/null; then
                open "$latest_report"
            fi
        fi
        ;;
    3)
        # التحقق من وجود Chrome/Chromium
        if ! command -v google-chrome &> /dev/null && ! command -v chromium-browser &> /dev/null; then
            echo "❌ Chrome/Chromium غير مثبت."
            echo "لتثبيت Chrome:"
            echo "- macOS: brew install --cask google-chrome"
            echo "- Ubuntu: wget -q -O - https://dl.google.com/linux/linux_signing_key.pub | sudo apt-key add -"
            echo "          sudo sh -c 'echo \"deb [arch=amd64] http://dl.google.com/linux/chrome/deb/ stable main\" >> /etc/apt/sources.list.d/google-chrome.list'"
            echo "          sudo apt update && sudo apt install google-chrome-stable"
            exit 1
        fi

        echo "📦 تثبيت المكتبات المطلوبة..."
        pip3 install -r requirements.txt

        echo "🔍 تشغيل الفحص الشامل مع JavaScript..."
        python3 website_checker.py

        echo ""
        echo "✅ انتهى الفحص! تحقق من مجلد reports للحصول على التقارير."
        echo ""
        echo "📊 التقارير المتاحة:"
        ls -la reports/ | grep -E "\.(html|csv|json|log)$" | tail -5

        echo ""
        echo "🌐 لعرض التقرير في المتصفح:"
        latest_report=$(ls -t reports/report_*.html 2>/dev/null | head -1)
        if [ -n "$latest_report" ]; then
            echo "open $latest_report"
            # فتح التقرير تلقائياً على macOS
            if command -v open &> /dev/null; then
                open "$latest_report"
            fi
        fi
        ;;
    *)
        echo "❌ اختيار غير صحيح. يرجى اختيار رقم من 1 إلى 3."
        exit 1
        ;;
esac

echo ""
echo "🎯 نصائح:"
echo "- استخدم الفحص السريع للمراقبة اليومية"
echo "- استخدم الفحص الشامل قبل النشر"
echo "- راجع التقارير HTML للحصول على تفاصيل أكثر"
