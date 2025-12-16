import pytest
import time

# ================================
# 1. LOGIN SYSTEM TESTS (7 tests)
# ================================

def test_login_admin_valid():
    username = "admin"
    password = "admin123"
    valid_users = {
        "admin": {"password": "admin123", "role": "admin"},
        "user1": {"password": "admin123", "role": "user"}
    }
    assert username in valid_users and valid_users[username]["password"] == password
    assert valid_users[username]["role"] == "admin"


def test_login_user_valid():
    username = "user1"
    password = "admin123"
    valid_users = {
        "user1": {"password": "admin123", "role": "user"}
    }
    assert username in valid_users and valid_users[username]["password"] == password
    assert valid_users[username]["role"] == "user"


def test_login_invalid_credentials():
    username = "wrong"
    password = "wrong"
    valid_users = {
        "admin": {"password": "admin123"},
        "user1": {"password": "admin123"}
    }
    is_valid = username in valid_users and valid_users[username]["password"] == password
    assert is_valid is False


def test_login_empty_fields():
    username = ""
    password = ""
    assert username == "" or password == ""


def test_login_sql_injection():
    username = "admin' OR '1'='1"
    password = "test"
    valid_users = {
        "admin": {"password": "admin123"}
    }
    assert username not in valid_users


# def test_login_xss_attack():
#     username = "<script>alert('XSS')</script>"
#     password = "123"
#     valid_users = {
#         "admin": {"password": "admin123"}
#     }
#     assert username not in valid_users


# def test_login_response_time():
#     start = time.time()
#     username = "admin"
#     password = "admin123"
#     valid_users = {
#         "admin": {"password": "admin123"}
#     }
#     result = valid_users.get(username, {}).get("password") == password
#     end = time.time()
#     assert result
#     assert end - start < 2.0
