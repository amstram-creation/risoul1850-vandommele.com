DROP TABLE IF EXISTS season_rules;
CREATE TABLE season_rules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL,              -- e.g. 'summer', 'winter_core'
  anchor VARCHAR(50) NULL,                -- 'easter'|'carnival'|'ascension'|'pentecost' OR 'MM-DD'
  start_offset_weeks INT NOT NULL DEFAULT 0,
  end_offset_weeks   INT NOT NULL DEFAULT 0,
  fixed_start_mmdd CHAR(5) NULL,          -- optional pure fixed range 'MM-DD'
  fixed_end_mmdd   CHAR(5) NULL,          -- optional pure fixed range 'MM-DD'
  active TINYINT(1) NOT NULL DEFAULT 1,
  KEY idx_active (active),
  KEY idx_anchor (anchor)
);

-- Summer: pure fixed range
INSERT INTO season_rules (name, fixed_start_mmdd, fixed_end_mmdd)
VALUES ('summer', '06-16', '09-15');

-- Easter family (algorithmic; anchor is a keyword)
INSERT INTO season_rules (name, anchor, start_offset_weeks, end_offset_weeks) VALUES
('easter',    'easter', 3, 3),
('carnival',  'easter', 3, 3),
('ascension', 'easter', 1, 1),
('pentecost', 'easter', 1, 1);

-- Fixed-date anchors (anchor is 'MM-DD')
INSERT INTO season_rules (name, anchor, start_offset_weeks, end_offset_weeks) VALUES
('winter',   '12-25', 2, 2),
('newyear', '12-31', 1, 2),  
('allsaints', '11-01', 1, 1);
