import pytest
import time
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

# ================================
# KONFIGURASI
# ================================
BASE_URL = "http://localhost/camping-rental-apps/login.php"   # UBAH SESUAI URL LOGIN KAMU

# ================================
# FIXTURE SELENIUM
# ================================
@pytest.fixture
def browser():
    options = webdriver.ChromeOptions()
    options.add_argument("--start-maximized")

    driver = webdriver.Chrome(
        service=Service(ChromeDriverManager().install()),
        options=options
    )
    yield driver
    driver.quit()

# ================================
# 1. LOGIN ADMIN VALID
# ================================
def test_login_admin_valid(browser):
    browser.get(BASE_URL)

    browser.find_element(By.NAME, "username").send_keys("admin")
    browser.find_element(By.NAME, "password").send_keys("admin123")
    browser.find_element(By.CSS_SELECTOR, ".btn-login").click()

    WebDriverWait(browser, 10).until(EC.url_contains("/admin"))
    assert "admin" in browser.current_url


# ================================
# 2. LOGIN USER VALID
# ================================
def test_login_user_valid(browser):
    browser.get(BASE_URL)

    browser.find_element(By.NAME, "username").send_keys("user1")
    browser.find_element(By.NAME, "password").send_keys("admin123")
    browser.find_element(By.CSS_SELECTOR, ".btn-login").click()

    WebDriverWait(browser, 10).until(EC.url_contains("/index"))
    assert "index" in browser.current_url


# ================================
# 3. INVALID CREDENTIALS
# ================================
def test_login_invalid_credentials(browser):
    browser.get(BASE_URL)

    browser.find_element(By.NAME, "username").send_keys("wronguser")
    browser.find_element(By.NAME, "password").send_keys("wrongpass")
    browser.find_element(By.CSS_SELECTOR, ".btn-login").click()

    alert = WebDriverWait(browser, 10).until(
        EC.presence_of_element_located((By.CLASS_NAME, "alert-danger"))
    )

    assert "success" not in alert.text.lower()


# ================================
# 4. EMPTY FIELDS
# ================================
def test_login_empty_fields(browser):
    browser.get(BASE_URL)

    username = browser.find_element(By.NAME, "username")
    password = browser.find_element(By.NAME, "password")
    submit_btn = browser.find_element(By.CSS_SELECTOR, ".btn-login")

    # Klik tombol submit tanpa isi input
    submit_btn.click()
    time.sleep(1)  # memberi waktu alert HTML5 muncul sebentar

    # Cek validitas form – jika invalid artinya alert muncul
    username_valid = username.get_property("validity")["valid"]
    password_valid = password.get_property("validity")["valid"]

    # Jika invalid = alert HTML5 muncul
    assert not username_valid or not password_valid, \
        "Seharusnya alert HTML5 'Harap isi bidang ini' muncul"

    print("✅ Alert HTML5 muncul → TEST BERHASIL")


# ================================
# 5. SQL INJECTION ATTACK
# ================================
def test_login_sql_injection(browser):
    browser.get(BASE_URL)

    browser.find_element(By.NAME, "username").send_keys("admin' OR '1'='1")
    browser.find_element(By.NAME, "password").send_keys("anything")
    browser.find_element(By.CSS_SELECTOR, ".btn-login").click()

    alert = WebDriverWait(browser, 10).until(
        EC.presence_of_element_located((By.CLASS_NAME, "alert-danger"))
    )

    assert "success" not in alert.text.lower()


# ================================
# 6. XSS ATTACK
# ================================
def test_login_xss_attack(browser):
    browser.get(BASE_URL)

    browser.find_element(By.NAME, "username").send_keys("<script>alert('XSS')</script>")
    browser.find_element(By.NAME, "password").send_keys("test123")
    browser.find_element(By.CSS_SELECTOR, ".btn-login").click()

    alert = WebDriverWait(browser, 10).until(
        EC.presence_of_element_located((By.CLASS_NAME, "alert-danger"))
    )

    assert "<script>" not in alert.text


# ================================
# 7. RESPONSE TIME PERFORMANCE TEST
# ================================
def test_login_response_time(browser):
    browser.get(BASE_URL)

    start = time.time()

    browser.find_element(By.NAME, "username").send_keys("admin")
    browser.find_element(By.NAME, "password").send_keys("admin123")
    browser.find_element(By.CSS_SELECTOR, ".btn-login").click()

    WebDriverWait(browser, 10).until(EC.url_contains("/admin"))

    duration = time.time() - start
    assert duration < 3, f"Response time terlalu lama: {duration:.2f}s"
