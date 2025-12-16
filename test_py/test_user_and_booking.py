import pytest
from datetime import datetime, timedelta
from decimal import Decimal

# ============================================
# MOCK DATA
# ============================================

USERS = [
    {
        "id": 1,
        "username": "admin",
        "password": "admin123",
        "role": "admin",
        "status": "active"
    },
    {
        "id": 3,
        "username": "syahrul",
        "password": "admin123",
        "role": "user",
        "status": "active"
    }
]

ITEMS = [
    {
        "id": 1,
        "name": "Tenda Tunnel 6 Orang",
        "category": "Tenda",
        "price_per_day": 250000,
        "quantity_available": 3,
        "quantity_total": 3,
        "status": "available"
    },
    {
        "id": 2,
        "name": "Sleeping Bag",
        "category": "Perlengkapan Tidur",
        "price_per_day": 40000,
        "quantity_available": 0,
        "quantity_total": 10,
        "status": "unavailable"
    }
]

BOOKINGS = [
    {
        "id": 1,
        "user_id": 3,
        "item_id": 1,
        "start_date": "2024-12-15",
        "end_date": "2024-12-17",
        "quantity": 1,
        "total_price": 500000,
        "status": "confirmed"
    }
]

PAYMENTS = [
    {
        "id": 1,
        "booking_id": 1,
        "user_id": 3,
        "amount": 500000,
        "status": "completed"
    }
]

REVIEWS = [
    {
        "id": 1,
        "booking_id": 1,
        "user_id": 3,
        "item_id": 1,
        "rating": 5,
        "comment": "Tenda sangat bagus"
    }
]

INVOICES = [
    {
        "id": 1,
        "booking_id": 1,
        "user_id": 3,
        "subtotal": 500000,
        "tax": 50000,
        "discount": 0,
        "total": 550000,
        "status": "paid"
    }
]

INVENTORY_HISTORY = [
    {
        "item_id": 1,
        "quantity_before": 4,
        "quantity_after": 3,
        "action": "booking"
    }
]

# ============================================
# TEST: USERS
# ============================================

def test_admin_exists():
    admin = next(u for u in USERS if u["username"] == "admin")
    assert admin["role"] == "admin"
    assert admin["id"] == 1


def test_user_exists():
    user = next(u for u in USERS if u["username"] == "syahrul")
    assert user["role"] == "user"
    assert user["id"] == 3


@pytest.mark.parametrize(
    "username,password,expected_role",
    [
        ("admin", "admin123", "admin"),
        ("syahrul", "admin123", "user"),
    ]
)
def test_login_mock(username, password, expected_role):
    user = next((u for u in USERS if u["username"] == username), None)
    assert user is not None
    assert user["password"] == password
    assert user["role"] == expected_role


# ============================================
# TEST: ITEMS
# ============================================

def test_items_exist():
    assert len(ITEMS) > 0


def test_item_price_positive():
    for item in ITEMS:
        assert item["price_per_day"] > 0


def test_item_quantity_valid():
    for item in ITEMS:
        assert item["quantity_available"] <= item["quantity_total"]


def test_get_available_items():
    available_items = [
        i for i in ITEMS
        if i["status"] == "available" and i["quantity_available"] > 0
    ]
    assert len(available_items) > 0


# ============================================
# TEST: BOOKINGS
# ============================================

def test_booking_user_exists():
    for booking in BOOKINGS:
        user_ids = [u["id"] for u in USERS]
        assert booking["user_id"] in user_ids


def test_booking_item_exists():
    for booking in BOOKINGS:
        item_ids = [i["id"] for i in ITEMS]
        assert booking["item_id"] in item_ids


def test_booking_date_valid():
    for booking in BOOKINGS:
        start = datetime.strptime(booking["start_date"], "%Y-%m-%d")
        end = datetime.strptime(booking["end_date"], "%Y-%m-%d")
        assert end >= start


def test_booking_status_valid():
    valid_status = ["pending", "confirmed", "completed", "cancelled"]
    for booking in BOOKINGS:
        assert booking["status"] in valid_status


# ============================================
# TEST: PAYMENTS
# ============================================

def test_payment_amount_positive():
    for payment in PAYMENTS:
        assert payment["amount"] > 0


def test_payment_has_booking():
    booking_ids = [b["id"] for b in BOOKINGS]
    for payment in PAYMENTS:
        assert payment["booking_id"] in booking_ids


# ============================================
# TEST: REVIEWS
# ============================================

def test_review_rating_range():
    for review in REVIEWS:
        assert 1 <= review["rating"] <= 5


# ============================================
# TEST: INVOICES
# ============================================

def test_invoice_total_calculation():
    for invoice in INVOICES:
        expected_total = invoice["subtotal"] + invoice["tax"] - invoice["discount"]
        assert invoice["total"] == expected_total


def test_invoice_status_valid():
    valid_status = ["draft", "sent", "paid", "cancelled"]
    for invoice in INVOICES:
        assert invoice["status"] in valid_status


# ============================================
# TEST: INVENTORY HISTORY
# ============================================

def test_inventory_tracking():
    for history in INVENTORY_HISTORY:
        assert history["quantity_before"] != history["quantity_after"]


# ============================================
# TEST: BUSINESS LOGIC
# ============================================

def test_user_can_book_available_item():
    item = next(i for i in ITEMS if i["status"] == "available")
    assert item["quantity_available"] > 0


def test_completed_booking_has_payment():
    completed = [b for b in BOOKINGS if b["status"] == "confirmed"]
    booking_ids_with_payment = [p["booking_id"] for p in PAYMENTS]

    for booking in completed:
        assert booking["id"] in booking_ids_with_payment
