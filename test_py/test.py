import pytest
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.service import Service
from webdriver_manager.chrome import ChromeDriverManager
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

BASE_URL = "http://localhost/camping-rental-apps/login.php"

# ================================
# FIXTURE
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
# HELPER
# ================================
def click_login(browser):
    WebDriverWait(browser, 10).until(
        EC.element_to_be_clickable((By.CSS_SELECTOR, ".btn-login"))
    ).click()


# ================================
# 1. LOGIN VALID
# ================================
def test_1_login_valid(browser):
    print("\n[TEST 1] Login valid")
    browser.get(BASE_URL)

    browser.find_element(By.NAME, "username").send_keys("admin")
    browser.find_element(By.NAME, "password").send_keys("admin123")
    click_login(browser)

    WebDriverWait(browser, 10).until(EC.url_contains("/admin"))
    assert "admin" in browser.current_url
    print("[PASS] Login valid berhasil")


# ================================
# 2. INVALID CREDENTIALS
# ================================
def test_2_invalid_credentials(browser):
    print("\n[TEST 2] Invalid credentials")
    browser.get(BASE_URL)

    browser.find_element(By.NAME, "username").send_keys("wronguser")
    browser.find_element(By.NAME, "password").send_keys("wrongpass")
    click_login(browser)

    alert = WebDriverWait(browser, 10).until(
        EC.presence_of_element_located((By.CLASS_NAME, "alert-danger"))
    )

    assert alert.is_displayed()
    print("[PASS] Login ditolak (invalid credentials)")
    
    



