DROP TABLE IF EXISTS week;

CREATE TABLE week (
    id INT AUTO_INCREMENT PRIMARY KEY,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    week_start DATE NOT NULL UNIQUE,

    -- pricing & flags
    price DECIMAL(8,2) NULL,

    -- booking status
    booked_at TIMESTAMP NULL,   
    confirmed TINYINT NULL,   -- NULL=available, 0=pending, 1=confirmed

    -- guest fields (nullable when not booked)
    guest_name  VARCHAR(255) NULL,
    guest_email VARCHAR(255) NULL,
    guest_phone VARCHAR(30)  NULL,

    -- Data consistency:
    --  - available (confirmed IS NULL): no guest data, no booked_at
    --  - pending/confirmed (0 or 1): require guest_name and booked_at
    CONSTRAINT chk_week_consistency
        CHECK (
          (confirmed IS NULL
            AND guest_name  IS NULL
            AND guest_email IS NULL
            AND guest_phone IS NULL
            AND booked_at   IS NULL)
          OR
          (confirmed IN (0,1)
            AND guest_name IS NOT NULL
            AND booked_at  IS NOT NULL)
        )
);

CREATE TABLE `price` (
  `is_high` tinyint(1) NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  PRIMARY KEY (`is_high`)
);

INSERT INTO `price` (`is_high`, `amount`) VALUES
(0, 850.00),
(1, 1370.00);

CREATE TABLE `high_season` (
  `date_start` date NOT NULL,
  `date_stop` date NOT NULL,
  PRIMARY KEY (`date_start`)
);


WITH RECURSIVE week_dates AS (
  SELECT DATE('2024-01-01') as week_start, 0 as week_num  -- replace date parameter
  UNION ALL
  SELECT DATE_ADD(week_start, INTERVAL 7 DAY), week_num + 1
  FROM week_dates WHERE week_num < 7
)
SELECT 
  wd.week_start,
  w.id,
  COALESCE(
    w.price,
    (SELECT amount FROM price WHERE is_high = 
      CASE WHEN EXISTS(
        SELECT 1 FROM high_season 
        WHERE wd.week_start BETWEEN date_start AND date_stop
      ) THEN 1 ELSE 0 END)
  ) as price,
  w.confirmed,
  w.guest_name,
  w.guest_email, 
  w.guest_phone,
  w.booked_at
FROM week_dates wd
LEFT JOIN week w ON wd.week_start = w.week_start
ORDER BY wd.week_start;