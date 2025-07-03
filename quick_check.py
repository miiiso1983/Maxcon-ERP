#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
MAXCON ERP Quick Website Checker
فحص سريع لأهم روابط الموقع
"""

import requests
import time
from datetime import datetime

def quick_check():
    """فحص سريع للروابط الأساسية"""
    
    base_url = "http://localhost:8000"
    
    # الروابط الأساسية للفحص السريع
    essential_urls = [
        "/",
        "/dashboard", 
        "/inventory",
        "/sales",
        "/customers",
        "/suppliers",
        "/financial",
        "/reports",
        "/ai",
        "/hr",
        "/medical-reps",
        "/compliance",
        "/analytics",
        "/performance",
        "/whatsapp"
    ]
    
    print("🚀 فحص سريع لموقع MAXCON ERP")
    print("=" * 50)
    
    successful = 0
    failed = 0
    start_time = time.time()
    
    for route in essential_urls:
        url = base_url + route
        try:
            response = requests.get(url, timeout=5)
            if response.status_code == 200:
                print(f"✅ {route} - OK ({response.elapsed.total_seconds():.3f}s)")
                successful += 1
            else:
                print(f"❌ {route} - HTTP {response.status_code}")
                failed += 1
        except Exception as e:
            print(f"❌ {route} - خطأ: {str(e)}")
            failed += 1
        
        time.sleep(0.1)  # تأخير قصير
    
    duration = time.time() - start_time
    total = successful + failed
    success_rate = (successful / total) * 100 if total > 0 else 0
    
    print("\n" + "=" * 50)
    print(f"📊 النتائج:")
    print(f"   إجمالي الروابط: {total}")
    print(f"   ناجح: {successful}")
    print(f"   فاشل: {failed}")
    print(f"   معدل النجاح: {success_rate:.1f}%")
    print(f"   مدة الفحص: {duration:.1f} ثانية")
    print("=" * 50)
    
    if success_rate >= 90:
        print("🎉 الموقع يعمل بشكل ممتاز!")
    elif success_rate >= 70:
        print("⚠️  الموقع يعمل بشكل جيد مع بعض المشاكل")
    else:
        print("🚨 الموقع يحتاج إلى إصلاحات عاجلة")

if __name__ == "__main__":
    quick_check()
