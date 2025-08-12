DROP TABLE IF EXISTS week;

CREATE TABLE week (
    id INT AUTO_INCREMENT PRIMARY KEY,
    week_start DATE NOT NULL UNIQUE,

    -- pricing & flags
    price DECIMAL(8,2) NOT NULL,
    is_high_season BOOLEAN NOT NULL DEFAULT FALSE,

    -- booking status
    confirmed TINYINT NULL,  -- NULL=pending, 0=no, 1=yes

    -- guest fields (nullable when not booked)
    guest_name  VARCHAR(255) NULL,
    guest_email VARCHAR(255) NULL,
    guest_phone VARCHAR(30)  NULL,

    booked_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Keep data consistent (MySQL 8.0+ enforces CHECK):
    -- If booked, require at least a name and booking_date.
    CONSTRAINT chk_booked_requires_guest
        CHECK (
          (is_booked = 0 AND guest_name IS NULL AND booking_date IS NULL AND confirmed IS NULL
           AND guest_email IS NULL AND guest_phone IS NULL)
          OR
          (is_booked = 1 AND guest_name IS NOT NULL AND booking_date IS NOT NULL)
        )
);


6uGagd)-aNCbW3A(