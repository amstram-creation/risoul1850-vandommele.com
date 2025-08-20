CREATE OR REPLACE VIEW week_summary AS
SELECT 
    COUNT(*) AS total,
    COUNT(CASE WHEN confirmed = 1 THEN 1 END) AS confirmed,
    COUNT(CASE WHEN guest_name IS NOT NULL AND confirmed != 1 THEN 1 END) AS pending,
    SUM(CASE WHEN confirmed = 1 THEN price ELSE 0 END) AS confirmed_revenue,
    SUM(price) AS total_revenue
FROM week;
