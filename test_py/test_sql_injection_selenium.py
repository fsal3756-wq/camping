"""
test_sql_injection_selenium.py
SQL Injection Testing menggunakan Pytest + Selenium
Testing berbagai metode SQL Injection pada Login Form

Install:
pip install pytest selenium webdriver-manager pytest-html

Run:
pytest test_sql_injection_selenium.py -v --html=report_sqli.html --self-contained-html
"""

import pytest
import time
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.service import Service
from selenium.common.exceptions import TimeoutException, NoSuchElementException
from webdriver_manager.chrome import ChromeDriverManager

# Configuration
BASE_URL = "http://localhost/camping-rental-apps"
LOGIN_URL = f"{BASE_URL}/login.php"


@pytest.fixture(scope="function")
def driver():
    """Setup Chrome WebDriver"""
    options = webdriver.ChromeOptions()
    options.add_argument('--start-maximized')
    options.add_argument('--disable-blink-features=AutomationControlled')
    # options.add_argument('--headless')  # Uncomment untuk headless mode
    
    service = Service(ChromeDriverManager().install())
    driver = webdriver.Chrome(service=service, options=options)
    driver.implicitly_wait(5)
    
    yield driver
    
    driver.quit()


class TestSQLInjection:
    """Test Suite untuk SQL Injection Testing"""
    
    # ========================================
    # 1. BASIC SQL INJECTION TESTS
    # ========================================
    
    def test_sqli_01_classic_or_bypass(self, driver):
        """
        Test 1: Classic OR 1=1 Bypass
        Payload: admin' OR '1'='1
        Expected: Login GAGAL (blocked)
        """
        driver.get(LOGIN_URL)
        time.sleep(1)
        
        print(f"\n🔍 Testing URL: {driver.current_url}")
        print(f"🔍 Page Title: {driver.title}")
        
        # Input payload
        try:
            username_field = driver.find_element(By.NAME, "username")
            password_field = driver.find_element(By.NAME, "password")
            
            print(f"✅ Form fields found")
            
            username_field.clear()
            password_field.clear()
            
            payload = "admin' OR '1'='1"
            username_field.send_keys(payload)
            password_field.send_keys("anything")
            
            print(f"📝 Payload entered: {payload}")
            
            # Submit
            submit_button = driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
            submit_button.click()
            
            print(f"🖱️  Form submitted, waiting for response...")
            time.sleep(3)
            
            # Check current URL after submit
            current_url = driver.current_url
            print(f"🔍 Current URL after submit: {current_url}")
            
            # Verify: Harus tetap di login page (GAGAL login)
            if "login.php" in current_url:
                print(f"✅ Still on login page - Good!")
                
                # Check error message
                try:
                    # Try multiple selectors
                    error = None
                    try:
                        error = driver.find_element(By.CLASS_NAME, "alert-danger")
                    except:
                        try:
                            error = driver.find_element(By.CSS_SELECTOR, ".alert.alert-danger")
                        except:
                            error = driver.find_element(By.XPATH, "//*[contains(@class, 'alert')]")
                    
                    if error:
                        error_text = error.text
                        print(f"📋 Error message: {error_text}")
                        
                        if "salah" in error_text.lower() or "wrong" in error_text.lower() or "invalid" in error_text.lower():
                            print("✅ PASS - Classic OR bypass BLOCKED")
                            assert True
                        else:
                            pytest.fail(f"Error message tidak sesuai: {error_text}")
                    else:
                        pytest.fail("Error element found but no text")
                        
                except NoSuchElementException as e:
                    print(f"⚠️  No error message found")
                    print(f"📄 Page source preview: {driver.page_source[:500]}")
                    # Masih PASS jika tetap di login page (berarti blocked)
                    print("✅ PASS - SQL Injection BLOCKED (stayed on login page)")
                    assert True
            else:
                # Jika redirect keluar dari login page = VULNERABLE!
                print(f"❌ FAIL - Redirected to: {current_url}")
                print(f"🚨 SQL INJECTION BERHASIL BYPASS! VULNERABLE!")
                pytest.fail(f"SQL Injection berhasil bypass! Redirected to: {current_url}")
                
        except NoSuchElementException as e:
            print(f"❌ Element not found: {e}")
            print(f"📄 Page source: {driver.page_source[:1000]}")
            pytest.fail(f"Element not found: {e}")
    
    
    def test_sqli_02_or_true_bypass(self, driver):
        """
        Test 2: OR 1=1 Bypass
        Payload: ' OR 1=1--
        Expected: Login GAGAL
        """
        driver.get(LOGIN_URL)
        
        username_field = driver.find_element(By.NAME, "username")
        password_field = driver.find_element(By.NAME, "password")
        
        username_field.send_keys("' OR 1=1--")
        password_field.send_keys("test")
        
        submit_button = driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
        submit_button.click()
        
        time.sleep(2)
        
        current_url = driver.current_url
        assert "login.php" in current_url
        
        try:
            error = driver.find_element(By.CLASS_NAME, "alert-danger")
            print("✅ PASS - OR 1=1 bypass BLOCKED")
        except NoSuchElementException:
            pytest.fail("Vulnerability detected!")
    
    
    def test_sqli_03_comment_bypass(self, driver):
        """
        Test 3: Comment Bypass
        Payload: admin'--
        Expected: Login GAGAL
        """
        driver.get(LOGIN_URL)
        
        username_field = driver.find_element(By.NAME, "username")
        password_field = driver.find_element(By.NAME, "password")
        
        username_field.send_keys("admin'--")
        password_field.send_keys("anything")
        
        submit_button = driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
        submit_button.click()
        
        time.sleep(2)
        
        assert "login.php" in driver.current_url
        print("✅ PASS - Comment bypass BLOCKED")
    
    
    def test_sqli_04_union_select_bypass(self, driver):
        """
        Test 4: UNION SELECT Bypass
        Payload: ' UNION SELECT NULL, username, password FROM users--
        Expected: Login GAGAL
        """
        driver.get(LOGIN_URL)
        
        username_field = driver.find_element(By.NAME, "username")
        password_field = driver.find_element(By.NAME, "password")
        
        username_field.send_keys("' UNION SELECT NULL, username, password FROM users--")
        password_field.send_keys("test")
        
        submit_button = driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
        submit_button.click()
        
        time.sleep(2)
        
        assert "login.php" in driver.current_url
        print("✅ PASS - UNION SELECT bypass BLOCKED")
    
    
    # ========================================
    # 2. ADVANCED SQL INJECTION TESTS
    # ========================================
    
    def test_sqli_05_boolean_based_blind(self, driver):
        """
        Test 5: Boolean-based Blind SQL Injection
        Payload: admin' AND '1'='1
        Expected: Login GAGAL
        """
        driver.get(LOGIN_URL)
        
        username_field = driver.find_element(By.NAME, "username")
        password_field = driver.find_element(By.NAME, "password")
        
        username_field.send_keys("admin' AND '1'='1")
        password_field.send_keys("test")
        
        submit_button = driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
        submit_button.click()
        
        time.sleep(2)
        
        assert "login.php" in driver.current_url
        print("✅ PASS - Boolean-based blind BLOCKED")
    
    
    def test_sqli_06_time_based_blind(self, driver):
        """
        Test 6: Time-based Blind SQL Injection
        Payload: admin'; WAITFOR DELAY '00:00:05'--
        Expected: Login GAGAL, response normal (tidak delay)
        """
        driver.get(LOGIN_URL)
        
        start_time = time.time()
        
        username_field = driver.find_element(By.NAME, "username")
        password_field = driver.find_element(By.NAME, "password")
        
        username_field.send_keys("admin'; WAITFOR DELAY '00:00:05'--")
        password_field.send_keys("test")
        
        submit_button = driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
        submit_button.click()
        
        time.sleep(2)
        
        end_time = time.time()
        response_time = end_time - start_time
        
        # Response harus cepat (< 3 detik), tidak delay 5 detik
        assert response_time < 4, f"Time-based injection detected! Response: {response_time}s"
        assert "login.php" in driver.current_url
        print(f"✅ PASS - Time-based blind BLOCKED (Response: {response_time:.2f}s)")
    
    
    def test_sqli_07_stacked_queries(self, driver):
        """
        Test 7: Stacked Queries Injection
        Payload: admin'; DROP TABLE users--
        Expected: Login GAGAL, table tidak terhapus
        """
        driver.get(LOGIN_URL)
        
        username_field = driver.find_element(By.NAME, "username")
        password_field = driver.find_element(By.NAME, "password")
        
        username_field.send_keys("admin'; DROP TABLE users--")
        password_field.send_keys("test")
        
        submit_button = driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
        submit_button.click()
        
        time.sleep(2)
        
        assert "login.php" in driver.current_url
        print("✅ PASS - Stacked queries BLOCKED")
    
    
    # ========================================
    # 3. ENCODING & OBFUSCATION TESTS
    # ========================================
    
    def test_sqli_08_hex_encoding(self, driver):
        """
        Test 8: Hex Encoding Bypass
        Payload: 0x61646d696e (hex untuk 'admin')
        Expected: Login GAGAL
        """
        driver.get(LOGIN_URL)
        
        username_field = driver.find_element(By.NAME, "username")
        password_field = driver.find_element(By.NAME, "password")
        
        username_field.send_keys("0x61646d696e' OR '1'='1")
        password_field.send_keys("test")
        
        submit_button = driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
        submit_button.click()
        
        time.sleep(2)
        
        assert "login.php" in driver.current_url
        print("✅ PASS - Hex encoding bypass BLOCKED")
    
    
    def test_sqli_09_char_encoding(self, driver):
        """
        Test 9: CHAR() Encoding
        Payload: admin' OR CHAR(49)=CHAR(49)--
        Expected: Login GAGAL
        """
        driver.get(LOGIN_URL)
        
        username_field = driver.find_element(By.NAME, "username")
        password_field = driver.find_element(By.NAME, "password")
        
        username_field.send_keys("admin' OR CHAR(49)=CHAR(49)--")
        password_field.send_keys("test")
        
        submit_button = driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
        submit_button.click()
        
        time.sleep(2)
        
        assert "login.php" in driver.current_url
        print("✅ PASS - CHAR encoding bypass BLOCKED")
    
    
    def test_sqli_10_concat_bypass(self, driver):
        """
        Test 10: CONCAT() Bypass
        Payload: admin' OR username=CONCAT('ad','min')--
        Expected: Login GAGAL
        """
        driver.get(LOGIN_URL)
        
        username_field = driver.find_element(By.NAME, "username")
        password_field = driver.find_element(By.NAME, "password")
        
        username_field.send_keys("admin' OR username=CONCAT('ad','min')--")
        password_field.send_keys("test")
        
        submit_button = driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
        submit_button.click()
        
        time.sleep(2)
        
        assert "login.php" in driver.current_url
        print("✅ PASS - CONCAT bypass BLOCKED")
    
    
    # ========================================
    # 4. PASSWORD FIELD INJECTION
    # ========================================
    
    def test_sqli_11_password_field_injection(self, driver):
        """
        Test 11: SQL Injection pada Password Field
        Payload: ' OR '1'='1
        Expected: Login GAGAL
        """
        driver.get(LOGIN_URL)
        
        username_field = driver.find_element(By.NAME, "username")
        password_field = driver.find_element(By.NAME, "password")
        
        username_field.send_keys("admin")
        password_field.send_keys("' OR '1'='1")
        
        submit_button = driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
        submit_button.click()
        
        time.sleep(2)
        
        assert "login.php" in driver.current_url
        print("✅ PASS - Password field injection BLOCKED")
    
    
    def test_sqli_12_both_fields_injection(self, driver):
        """
        Test 12: SQL Injection pada Both Fields
        Expected: Login GAGAL
        """
        driver.get(LOGIN_URL)
        
        username_field = driver.find_element(By.NAME, "username")
        password_field = driver.find_element(By.NAME, "password")
        
        username_field.send_keys("admin' OR '1'='1")
        password_field.send_keys("' OR '1'='1")
        
        submit_button = driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
        submit_button.click()
        
        time.sleep(2)
        
        assert "login.php" in driver.current_url
        print("✅ PASS - Both fields injection BLOCKED")
    
    
    # ========================================
    # 5. SPECIAL CASES
    # ========================================
    
    def test_sqli_13_case_variation(self, driver):
        """
        Test 13: Case Variation Bypass
        Payload: AdMiN' oR '1'='1
        Expected: Login GAGAL
        """
        driver.get(LOGIN_URL)
        
        username_field = driver.find_element(By.NAME, "username")
        password_field = driver.find_element(By.NAME, "password")
        
        username_field.send_keys("AdMiN' oR '1'='1")
        password_field.send_keys("test")
        
        submit_button = driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
        submit_button.click()
        
        time.sleep(2)
        
        assert "login.php" in driver.current_url
        print("✅ PASS - Case variation BLOCKED")
    
    
    def test_sqli_14_whitespace_manipulation(self, driver):
        """
        Test 14: Whitespace Manipulation
        Payload: admin'/**/OR/**/'1'='1
        Expected: Login GAGAL
        """
        driver.get(LOGIN_URL)
        
        username_field = driver.find_element(By.NAME, "username")
        password_field = driver.find_element(By.NAME, "password")
        
        username_field.send_keys("admin'/**/OR/**/'1'='1")
        password_field.send_keys("test")
        
        submit_button = driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
        submit_button.click()
        
        time.sleep(2)
        
        assert "login.php" in driver.current_url
        print("✅ PASS - Whitespace manipulation BLOCKED")
    
    
    def test_sqli_15_null_byte_injection(self, driver):
        """
        Test 15: Null Byte Injection
        Payload: admin%00' OR '1'='1
        Expected: Login GAGAL
        """
        driver.get(LOGIN_URL)
        
        username_field = driver.find_element(By.NAME, "username")
        password_field = driver.find_element(By.NAME, "password")
        
        username_field.send_keys("admin\x00' OR '1'='1")
        password_field.send_keys("test")
        
        submit_button = driver.find_element(By.CSS_SELECTOR, "button[type='submit']")
        submit_button.click()
        
        time.sleep(2)
        
        assert "login.php" in driver.current_url
        print("✅ PASS - Null byte injection BLOCKED")


# ========================================
# SUMMARY REPORT
# ========================================

def pytest_terminal_summary(terminalreporter, exitstatus, config):
    """Custom summary setelah test selesai"""
    print("\n" + "="*60)
    print("SQL INJECTION TESTING SUMMARY")
    print("="*60)
    
    passed = len(terminalreporter.stats.get('passed', []))
    failed = len(terminalreporter.stats.get('failed', []))
    total = passed + failed
    
    print(f"\nTotal Tests: {total}")
    print(f"Passed: {passed} (Security tests yang berhasil block SQLi)")
    print(f"Failed: {failed} (VULNERABILITIES DETECTED!)")
    
    if failed > 0:
        print("\n⚠️  WARNING: SQL Injection vulnerabilities detected!")
        print("Please review failed tests and fix the vulnerabilities.")
    else:
        print("\n✅ EXCELLENT: All SQL injection attempts were blocked!")
        print("The application is secure against tested SQL injection methods.")
    
    print("="*60)


if __name__ == "__main__":
    pytest.main([__file__, "-v", "--html=report_sqli.html", "--self-contained-html"])