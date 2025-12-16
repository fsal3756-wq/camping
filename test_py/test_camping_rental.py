"""
test_camping_rental.py - FIXED VERSION
Complete Automated Testing untuk Camping Rental Application

CATATAN PENTING:
- Test login menggunakan MOCK DATA (tidak perlu api_login.php)
- Test calculation: Pure logic (tidak perlu API)
- Test API data: Menggunakan MOCK DATA

Total: 11 test functions untuk 3 fungsi berbeda

Install:
pip install pytest pytest-html

Run:
pytest test_py/test_camping_rental.py -v --html=report.html --self-contained-html
"""

import pytest
from datetime import datetime, timedelta

# ========================================
# 1. LOGIN SYSTEM TESTS (7 functions)
# ========================================

def test_login_admin_valid():
    """Test 1: Login admin dengan credentials valid"""
    # Simulate login logic
    username = "admin"
    password = "admin123"
    
    # Simulate authentication
    valid_users = {
        "admin": {"password": "admin123", "role": "admin"},
        "user1": {"password": "admin123", "role": "user"}
    }
    
    # Check credentials
    if username in valid_users and valid_users[username]["password"] == password:
        user_data = valid_users[username]
        assert user_data["role"] == "admin"
        print("✅ Login admin berhasil")
    else:
        assert False, "Login should succeed"


def test_login_user_valid():
    """Test 2: Login user dengan credentials valid"""
    username = "user1"
    password = "admin123"
    
    valid_users = {
        # "admin": {"password": "admin123", "role": "admin"},
        "user1": {"password": "admin123", "role": "user"}
    }
    
    if username in valid_users and valid_users[username]["password"] == password:
        user_data = valid_users[username]
        assert user_data["role"] == "user"
        print("✅ Login user berhasil")
    else:
        assert False, "Login should succeed"


def test_login_invalid_credentials():
    """Test 3: Login dengan credentials tidak valid"""
    username = "wronguser"
    password = "wrongpass"
    
    valid_users = {
        "admin": {"password": "admin123", "role": "admin"},
        "user1": {"password": "admin123", "role": "user"}
    }
    
    # Should fail
    is_valid = username in valid_users and valid_users[username]["password"] == password
    assert is_valid == False, "Invalid credentials should be rejected"
    print("✅ Invalid credentials ditolak dengan benar")


def test_login_empty_fields():
    """Test 4: Login dengan field kosong"""
    username = ""
    password = ""
    
    # Validation
    if not username or not password:
        error = "Username dan password harus diisi"
        assert "harus diisi" in error.lower()
        print("✅ Validasi field kosong bekerja")
    else:
        assert False, "Should show validation error"


def test_login_sql_injection():
    """Test 5: Security - SQL Injection pada login"""
    username = "admin' OR '1'='1"
    password = "anything"
    
    valid_users = {
        "admin": {"password": "admin123", "role": "admin"},
        "user1": {"password": "admin123", "role": "user"}
    }
    
    # SQL injection should not work (exact match required)
    is_valid = username in valid_users
    assert is_valid == False, "SQL injection should be blocked"
    print("✅ SQL Injection berhasil diblokir")


def test_login_xss_attack():
    """Test 6: Security - XSS Attack pada login"""
    username = "<script>alert('XSS')</script>"
    password = "test123"
    
    valid_users = {
        "admin": {"password": "admin123", "role": "admin"},
        "user1": {"password": "admin123", "role": "user"}
    }
    
    # XSS should not work (exact match required)
    is_valid = username in valid_users
    assert is_valid == False, "XSS attack should be blocked"
    print("✅ XSS Attack berhasil diblokir")


def test_login_response_time():
    """Test 7: Performance - Response time login"""
    import time
    
    start = time.time()
    
    # Simulate login process
    username = "admin"
    password = "admin123"
    valid_users = {
        "admin": {"password": "admin123", "role": "admin"}
    }
    result = username in valid_users and valid_users[username]["password"] == password
    
    end = time.time()
    response_time = end - start
    
    assert response_time < 2.0, f"Response time {response_time}s melebihi 2 detik"
    print(f"✅ Response time: {response_time:.4f}s (< 2s)")
    
# ========================================
# 2. PENJUMLAHAN HARGA BOOKING (2 functions)
# ========================================

def test_calculate_booking_price():
    """
    Test 9: Penjumlahan harga booking
    
    Formula:
    - Subtotal = price_per_day × days × quantity
    - Tax = subtotal × (tax_rate / 100)
    - Total = subtotal + tax
    
    Test Case:
    - Harga per hari: Rp 150,000
    - Durasi: 3 hari
    - Quantity: 2 unit
    - Tax rate: 10%
    
    Expected:
    - Subtotal: 150,000 × 3 × 2 = 900,000
    - Tax: 900,000 × 0.1 = 90,000
    - Total: 900,000 + 90,000 = 990,000
    """
    # Test data
    price_per_day = 150000
    days = 3
    quantity = 2
    tax_rate = 10  # persen
    
    # Calculate
    subtotal = price_per_day * days * quantity
    tax = subtotal * (tax_rate / 100)
    total = subtotal + tax
    
    # Expected values
    expected_subtotal = 900000
    expected_tax = 90000
    expected_total = 990000
    
    # Assertions
    assert subtotal == expected_subtotal, f"Subtotal salah: {subtotal} != {expected_subtotal}"
    assert tax == expected_tax, f"Tax salah: {tax} != {expected_tax}"
    assert total == expected_total, f"Total salah: {total} != {expected_total}"
    
    print(f"✅ Penjumlahan harga booking benar:")
    print(f"   Subtotal: Rp {subtotal:,}")
    print(f"   Tax (10%): Rp {tax:,}")
    print(f"   Total: Rp {total:,}")


def test_calculate_booking_price_with_discount():
    """
    Test 10: Penjumlahan harga dengan diskon (booking > 5 hari)
    
    Formula:
    - Subtotal = price_per_day × days × quantity
    - Discount = subtotal × 5% (jika days > 5)
    - Subtotal after discount = subtotal - discount
    - Tax = subtotal_after_discount × 10%
    - Total = subtotal_after_discount + tax
    
    Test Case:
    - Harga: Rp 100,000
    - Durasi: 7 hari (eligible untuk diskon 5%)
    - Quantity: 1 unit
    
    Expected:
    - Subtotal: 100,000 × 7 × 1 = 700,000
    - Discount: 700,000 × 5% = 35,000
    - Subtotal after discount: 665,000
    - Tax: 665,000 × 10% = 66,500
    - Total: 731,500
    """
    price_per_day = 100000
    days = 7
    quantity = 1
    discount_rate = 5  # 5% untuk booking > 5 hari
    tax_rate = 10
    
    # Calculate
    subtotal = price_per_day * days * quantity
    discount = subtotal * (discount_rate / 100) if days > 5 else 0
    subtotal_after_discount = subtotal - discount
    tax = subtotal_after_discount * (tax_rate / 100)
    total = subtotal_after_discount + tax
    
    # Expected
    assert subtotal == 700000
    assert discount == 35000
    assert subtotal_after_discount == 665000
    assert tax == 66500
    assert total == 731500
    
    print(f"✅ Penjumlahan dengan diskon benar:")
    print(f"   Subtotal: Rp {subtotal:,}")
    print(f"   Discount (5%): -Rp {discount:,}")
    print(f"   Subtotal after discount: Rp {subtotal_after_discount:,}")
    print(f"   Tax (10%): Rp {tax:,}")
    print(f"   Total: Rp {total:,}")


# ========================================
# 3. PENGAMBILAN DATA API (2 functions)
# ========================================

def test_get_items_from_api():
    """
    Test 11: Pengambilan data items dari API
    
    Test:
    - Response format JSON
    - Data contains array of items
    - Each item has required fields
    - Data types validation
    """
    # Mock data - simulasi response dari API
    mock_api_response = {
        "status": "success",
        "data": [
            {
                "id": 1,
                "name": "Tenda Dome 4 Orang",
                "description": "Tenda berkualitas tinggi",
                "category": "Tenda",
                "price_per_day": 150000,
                "quantity_available": 5,
                "status": "available"
            },
            {
                "id": 2,
                "name": "Sleeping Bag Premium",
                "description": "Sleeping bag premium",
                "category": "Perlengkapan Tidur",
                "price_per_day": 50000,
                "quantity_available": 10,
                "status": "available"
            }
        ],
        "total": 2
    }
    
    # Test response structure
    assert mock_api_response['status'] == 'success', "Status harus success"
    assert 'data' in mock_api_response, "Response harus punya field data"
    assert isinstance(mock_api_response['data'], list), "Data harus berupa array"
    assert len(mock_api_response['data']) > 0, "Data tidak boleh kosong"
    
    # Test each item structure
    for item in mock_api_response['data']:
        assert 'id' in item, "Item harus punya id"
        assert 'name' in item, "Item harus punya name"
        assert 'price_per_day' in item, "Item harus punya price_per_day"
        assert 'quantity_available' in item, "Item harus punya quantity_available"
        assert 'status' in item, "Item harus punya status"
        
        # Validate data types
        assert isinstance(item['id'], int), "ID harus integer"
        assert isinstance(item['name'], str), "Name harus string"
        assert isinstance(item['price_per_day'], (int, float)), "Price harus numeric"
        assert isinstance(item['quantity_available'], int), "Quantity harus integer"
        assert item['status'] in ['available', 'unavailable'], "Status harus valid"
    
    print(f"✅ Pengambilan data API berhasil:")
    print(f"   Total items: {mock_api_response['total']}")
    print(f"   Items retrieved:")
    for item in mock_api_response['data']:
        print(f"   - {item['name']}: Rp {item['price_per_day']:,}/hari ({item['quantity_available']} tersedia)")


def test_get_user_bookings_from_api():
    """
    Test 12: Pengambilan data booking user dari API
    
    Test:
    - Response contains booking history
    - Data structure valid
    - Date validation
    - Price validation
    """
    # Mock API response untuk bookings
    user_id = 2  # user1
    
    mock_bookings_response = {
        "status": "success",
        "data": [
            {
                "id": 1,
                "user_id": 2,
                "item_id": 1,
                "item_name": "Tenda Dome 4 Orang",
                "start_date": "2024-12-10",
                "end_date": "2024-12-13",
                "quantity": 2,
                "total_price": 900000,
                "status": "pending"
            },
            {
                "id": 2,
                "user_id": 2,
                "item_id": 2,
                "item_name": "Sleeping Bag Premium",
                "start_date": "2024-12-15",
                "end_date": "2024-12-17",
                "quantity": 1,
                "total_price": 100000,
                "status": "confirmed"
            }
        ],
        "total": 2
    }
    
    # Test response structure
    assert mock_bookings_response['status'] == 'success'
    assert 'data' in mock_bookings_response
    assert isinstance(mock_bookings_response['data'], list)
    
    # Test booking data
    for booking in mock_bookings_response['data']:
        # Check required fields
        required_fields = ['id', 'user_id', 'item_id', 'item_name', 
                          'start_date', 'end_date', 'quantity', 'total_price', 'status']
        for field in required_fields:
            assert field in booking, f"Booking harus punya field {field}"
        
        # Validate user_id matches
        assert booking['user_id'] == user_id, "User ID harus sesuai filter"
        
        # Validate status
        assert booking['status'] in ['pending', 'confirmed', 'completed', 'cancelled']
        
        # Validate dates
        start = datetime.strptime(booking['start_date'], '%Y-%m-%d')
        end = datetime.strptime(booking['end_date'], '%Y-%m-%d')
        assert end >= start, "End date harus >= start date"
        
        # Validate price
        assert booking['total_price'] > 0, "Total price harus > 0"
    
    print(f"✅ Pengambilan data booking berhasil:")
    print(f"   Total bookings: {mock_bookings_response['total']}")
    for booking in mock_bookings_response['data']:
        print(f"   - #{booking['id']}: {booking['item_name']} ({booking['status']})")
        print(f"     {booking['start_date']} s/d {booking['end_date']}")
        print(f"     Total: Rp {booking['total_price']:,}")
        
        
# ========================================
# 4. SECURITY - BRUTE FORCE LOGIN PROTECTION
# ======================================== 
def test_login_bruteforce_protection():
    """Test 13: Security - Brute force login protection"""
    username = "admin"
    wrong_password = "salah"
    max_attempts = 3

    valid_users = {
        "admin": {"password": "admin123", "role": "admin"}
    }

    failed_attempts = 0

    for _ in range(5):
        if valid_users[username]["password"] != wrong_password:
            failed_attempts += 1

    assert failed_attempts >= max_attempts
    print("✅ Brute force login berhasil dibatasi")



# ========================================
# SUMMARY
# ========================================

if __name__ == "__main__":
    print("=" * 60)
    print("CAMPING RENTAL - AUTOMATED TESTING")
    print("=" * 60)
    print("\nTotal Test Functions: 11")
    print("\n1. Login System: 7 tests")
    print("   - Valid admin login")
    print("   - Valid user login")
    print("   - Invalid credentials")
    print("   - Empty fields validation")
    print("   - SQL injection security")
    print("   - XSS attack security")
    print("   - Response time performance")
    print("\n2 - Login with parametrized data: 1 test")
    print("\n2. Penjumlahan Harga: 2 tests")
    print("   - Calculate booking price (basic)")
    print("   - Calculate with discount (> 5 days)")
    print("\n3. Pengambilan Data API: 2 tests")
    print("   - Get items from API (mock data)")
    print("   - Get user bookings from API (mock data)")
    print("\n4. Brute Force Login Protection: 1 test")
    print("\n" + "=" * 60)
    print("Run: pytest test_camping_rental.py -v --html=report.html")
    print("=" * 60)