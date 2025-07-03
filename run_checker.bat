@echo off
chcp 65001 >nul
title MAXCON ERP Website Checker

echo 🚀 أداة فحص موقع MAXCON ERP
echo ==================================
echo اختر نوع الفحص:
echo 1) فحص سريع (15 رابط أساسي)
echo 2) فحص شامل بدون JavaScript
echo 3) فحص شامل مع JavaScript (يتطلب Chrome)
echo ==================================
set /p choice="اختر الرقم (1-3): "

REM التحقق من وجود Python
python --version >nul 2>&1
if errorlevel 1 (
    echo ❌ Python غير مثبت. يرجى تثبيت Python أولاً.
    echo يمكنك تحميله من: https://www.python.org/downloads/
    pause
    exit /b 1
)

REM إنشاء مجلد التقارير
if not exist reports mkdir reports

if "%choice%"=="1" (
    echo 🏃‍♂️ تشغيل الفحص السريع...
    python quick_check.py
) else if "%choice%"=="2" (
    echo 📦 التحقق من المكتبات المطلوبة...
    python -c "import requests, bs4" >nul 2>&1
    if errorlevel 1 (
        echo 📦 تثبيت المكتبات المطلوبة...
        pip install requests beautifulsoup4 lxml
    )
    
    echo 🔍 تشغيل الفحص الشامل...
    python simple_checker.py
    
    echo.
    echo ✅ انتهى الفحص! تحقق من مجلد reports للحصول على التقارير.
    echo.
    echo 📊 التقارير المتاحة:
    dir reports\*.html reports\*.csv reports\*.json /b 2>nul
    
    echo.
    echo 🌐 لعرض التقرير في المتصفح:
    for /f %%i in ('dir reports\report_*.html /b /o-d 2^>nul') do (
        echo start reports\%%i
        start reports\%%i
        goto :done
    )
    :done
) else if "%choice%"=="3" (
    REM التحقق من وجود Chrome
    where chrome >nul 2>&1
    if errorlevel 1 (
        where "C:\Program Files\Google\Chrome\Application\chrome.exe" >nul 2>&1
        if errorlevel 1 (
            echo ❌ Chrome غير مثبت.
            echo يرجى تحميل وتثبيت Google Chrome من:
            echo https://www.google.com/chrome/
            pause
            exit /b 1
        )
    )
    
    echo 📦 تثبيت المكتبات المطلوبة...
    pip install -r requirements.txt
    
    echo 🔍 تشغيل الفحص الشامل مع JavaScript...
    python website_checker.py
    
    echo.
    echo ✅ انتهى الفحص! تحقق من مجلد reports للحصول على التقارير.
    echo.
    echo 📊 التقارير المتاحة:
    dir reports\*.html reports\*.csv reports\*.json /b 2>nul
    
    echo.
    echo 🌐 لعرض التقرير في المتصفح:
    for /f %%i in ('dir reports\report_*.html /b /o-d 2^>nul') do (
        echo start reports\%%i
        start reports\%%i
        goto :done2
    )
    :done2
) else (
    echo ❌ اختيار غير صحيح. يرجى اختيار رقم من 1 إلى 3.
    pause
    exit /b 1
)

echo.
echo 🎯 نصائح:
echo - استخدم الفحص السريع للمراقبة اليومية
echo - استخدم الفحص الشامل قبل النشر
echo - راجع التقارير HTML للحصول على تفاصيل أكثر

pause
