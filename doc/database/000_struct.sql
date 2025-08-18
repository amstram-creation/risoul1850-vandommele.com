-- Table structure for table `operator`
--

CREATE TABLE `operator` (
  `id` int(10) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `enabled_at` timestamp NULL DEFAULT NULL,
  `revoked_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

--
-- Table structure for table `price`
--

CREATE TABLE `price` (
  `is_high` tinyint(1) NOT NULL,
  `amount` decimal(8,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `price`
--

INSERT INTO `price` (`is_high`, `amount`) VALUES
(0, 850.00),
(1, 1370.00);

-- --------------------------------------------------------

--
-- Table structure for table `week`
--

CREATE TABLE `week` (
  `id` int(11) NOT NULL,
  `week_start` date NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `is_high_season` tinyint(1) NOT NULL DEFAULT 0,
  `confirmed` tinyint(4) DEFAULT NULL,
  `guest_name` varchar(255) DEFAULT NULL,
  `guest_email` varchar(255) DEFAULT NULL,
  `guest_phone` varchar(30) DEFAULT NULL,
  `booked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `operator`
--
ALTER TABLE `operator`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `operator_uk_username` (`username`),
  ADD KEY `idx_operator_status` (`status`);

--
-- Indexes for table `price`
--
ALTER TABLE `price`
  ADD PRIMARY KEY (`is_high`);

--
-- Indexes for table `week`
--
ALTER TABLE `week`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `week_start` (`week_start`);

--
-- AUTO_INCREMENT for table `operator`
--
ALTER TABLE `operator`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `week`
--
ALTER TABLE `week`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
