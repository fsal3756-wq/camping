from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import time

BASE_URL = "http://localhost/camping-rental-apps/login.php"


# ================================
# HELPER FUNCTIONS
# ================================
def setup_browser():
    """Setup browser dengan ChromeDriver"""
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")
    
    driver = webdriver.Chrome(
        service=Service(ChromeDriverManager().install()),
        options=options
    )
    return driver

def click_login(driver):
    """Helper untuk click tombol login"""
    WebDriverWait(driver, 10).until(
        EC.element_to_be_clickable((By.CSS_SELECTOR, ".btn-login"))
    ).click()


# ================================
# TEST FUNCTIONS
# ================================

# ================================
# LOGIN VALID
# ================================
def test_1_login_valid():
    """Test 1: Login dengan kredensial valid"""
    print("\n" + "="*50)
    print("[TEST 1] Login Valid")
    print("="*50)
    
    driver = setup_browser()
    try:
        driver.get(BASE_URL)
        
        driver.find_element(By.NAME, "username").send_keys("admin")
        driver.find_element(By.NAME, "password").send_keys("admin123")
        click_login(driver)
        
        WebDriverWait(driver, 10).until(EC.url_contains("/admin"))
        
        if "admin" in driver.current_url:
            print("✓ [PASS] Login valid berhasil")
            print(f"  URL: {driver.current_url}")
        else:
            print("✗ [FAIL] Login valid gagal")
            
    except Exception as e:
        print(f"✗ [ERROR] {str(e)}")
    finally:
        driver.quit()
        time.sleep(1)

# ================================
# INVALID CREDENTIALS
# ================================
def test_2_invalid_credentials():
    """Test 2: Login dengan kredensial invalid"""
    print("\n" + "="*50)
    print("[TEST 2] Invalid Credentials")
    print("="*50)
    
    driver = setup_browser()
    try:
        driver.get(BASE_URL)
        
        driver.find_element(By.NAME, "username").send_keys("wronguser")
        driver.find_element(By.NAME, "password").send_keys("wrongpass")
        click_login(driver)
        
        alert = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CLASS_NAME, "alert-danger"))
        )
        
        if alert.is_displayed():
            print("✓ [PASS] Login ditolak (invalid credentials)")
            print(f"  Alert message: {alert.text}")
        else:
            print("✗ [FAIL] Alert tidak muncul")
            
    except Exception as e:
        print(f"✗ [ERROR] {str(e)}")
    finally:
        driver.quit()
        time.sleep(1)
        
        
# # # ================================
# # # SQL INJECTION
# # # ================================
def test_3_sql_injection():
    """Test 3: SQL Injection Attack"""
    print("\n" + "="*50)
    print("[TEST 3] SQL Injection")
    print("="*50)
    
    driver = setup_browser()
    try:
        driver.get(BASE_URL)
        
        driver.find_element(By.NAME, "username").send_keys("admin' OR '1'='1")
        driver.find_element(By.NAME, "password").send_keys("test")
        click_login(driver)
        
        alert = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CLASS_NAME, "alert-danger"))
        )
        
        if alert.is_displayed():
            print("✓ [PASS] SQL Injection berhasil diblokir")
            print(f"  Alert message: {alert.text}")
        else:
            print("✗ [FAIL] SQL Injection tidak diblokir")
            
    except Exception as e:
        print(f"✗ [ERROR] {str(e)}")
    finally:
        driver.quit()
        time.sleep(1)


# # # ================================
# # # XSS ATTACK
# # # ================================
def test_4_xss_attack():
    """Test 4: XSS Attack"""
    print("\n" + "="*50)
    print("[TEST 4] XSS Attack")
    print("="*50)
    
    driver = setup_browser()
    try:
        driver.get(BASE_URL)
        
        driver.find_element(By.NAME, "username").send_keys("<script>alert(1)</script>")
        driver.find_element(By.NAME, "password").send_keys("test")
        click_login(driver)
        
        alert = WebDriverWait(driver, 10).until(
            EC.presence_of_element_located((By.CLASS_NAME, "alert-danger"))
        )
        
        if "<script>" not in alert.text:
            print("✓ [PASS] XSS berhasil diblokir")
            print(f"  Alert message: {alert.text}")
        else:
            print("✗ [FAIL] XSS tidak diblokir (script tag terdeteksi)")
            
    except Exception as e:
        print(f"✗ [ERROR] {str(e)}")
    finally:
        driver.quit()
        time.sleep(1)


# ================================
# FIELD VALIDATIONS
# ================================
def test_5_empty_fields():
    """Test 5: Username & Password Kosong"""
    print("\n" + "="*50)
    print("[TEST 5] Username & Password Kosong")
    print("="*50)
    
    driver = setup_browser()
    try:
        driver.get(BASE_URL)
        
        click_login(driver)
        
        username = driver.find_element(By.NAME, "username")
        message = username.get_attribute("validationMessage")
        
        if message != "":
            print("✓ [PASS] Validasi browser muncul")
            print(f"  Validation message: '{message}'")
        else:
            print("✗ [FAIL] Validasi browser tidak muncul")
            
    except Exception as e:
        print(f"✗ [ERROR] {str(e)}")
    finally:
        driver.quit()
        time.sleep(1)


# ================================
# EMPTY USERNAME
# ================================
def test_6_empty_username():
    """Test 6: Username Kosong"""
    print("\n" + "="*50)
    print("[TEST 6] Username Kosong")
    print("="*50)
    
    driver = setup_browser()
    try:
        driver.get(BASE_URL)
        
        driver.find_element(By.NAME, "password").send_keys("admin123")
        click_login(driver)
        
        username = driver.find_element(By.NAME, "username")
        message = username.get_attribute("validationMessage")
        
        if message != "":
            print("✓ [PASS] Validasi browser muncul")
            print(f"  Validation message: '{message}'")
        else:
            print("✗ [FAIL] Validasi browser tidak muncul")
            
    except Exception as e:
        print(f"✗ [ERROR] {str(e)}")
    finally:
        driver.quit()
        time.sleep(1)


# ================================
# EMPTY PASSWORD
# ================================
def test_7_empty_password():
    """Test 7: Password Kosong"""
    print("\n" + "="*50)
    print("[TEST 7] Password Kosong")
    print("="*50)
    
    driver = setup_browser()
    try:
        driver.get(BASE_URL)
        
        driver.find_element(By.NAME, "username").send_keys("admin")
        click_login(driver)
        
        password = driver.find_element(By.NAME, "password")
        message = password.get_attribute("validationMessage")
        
        if message != "":
            print("✓ [PASS] Validasi browser muncul")
            print(f"  Validation message: '{message}'")
        else:
            print("✗ [FAIL] Validasi browser tidak muncul")
            
    except Exception as e:
        print(f"✗ [ERROR] {str(e)}")
    finally:
        driver.quit()
        time.sleep(1)


# ================================
# MAIN EXECUTION
# ================================
def run_all_tests():
    """Jalankan semua test"""
    # print("\n")
    print("╔" + "="*60 + "╗")
    print("║" + " "*15 + "LOGIN TESTING CAMPING RENTAL" + " "*17 + "║")
    print("╚" + "="*60 + "╝")
    
    tests = [
        test_1_login_valid,
        test_2_invalid_credentials,
        test_3_sql_injection,
        test_4_xss_attack,
        test_5_empty_fields,
        test_6_empty_username,
        test_7_empty_password
    ]
    
    passed = 0
    failed = 0
    
    for test in tests:
        try:
            test()
            passed += 1
        except Exception as e:
            print(f"✗ [FAILED] {test.__name__}: {str(e)}")
            failed += 1
    
    # Summary
    # print("\n" + "="*50)
    print("TEST SUMMARY")
    print("="*50)
    print(f"Total Tests: {len(tests)}")
    print(f"✓ Passed: {passed}")
    print(f"✗ Failed: {failed}")
    # print("="*50 + "\n")


if __name__ == "__main__":
    run_all_tests()