CREATE OR REPLACE VIEW week_with_status AS
SELECT 
    w.*, 
    CASE 
        WHEN w.confirmed = 1 THEN 'confirmed'
        WHEN w.confirmed = 0 THEN 'pending'
        WHEN w.guest_name IS NOT NULL THEN 'pending'
        ELSE 'available' 
    END AS status
FROM week w
ORDER BY w.week_start ASC;
