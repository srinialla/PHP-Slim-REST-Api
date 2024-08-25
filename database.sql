
CREATE TABLE `likes` (
  `ID_USER` int(10) UNSIGNED NOT NULL,
  `ID_QUOTE` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`ID_USER`, `ID_QUOTE`) VALUES
(1, 1),
(1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `quotes`
--

CREATE TABLE `quotes` (
  `ID_QUOTE` int(10) UNSIGNED NOT NULL,
  `QUOTE` varchar(500) NOT NULL,
  `POST_DATE` date NOT NULL,
  `POST_TIME` time NOT NULL,
  `LIKES` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `ID_USER` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `quotes`
--

INSERT INTO `quotes` (`ID_QUOTE`, `QUOTE`, `POST_DATE`, `POST_TIME`, `LIKES`, `ID_USER`) VALUES
(1, 'Successful people are not gifted; they just work hard, then succeed on purpose.', '2018-01-01', '00:00:00', 1, 1),
(2, 'Success is not final; failure is not fatal; It is the courage to continue that counts.', '0000-00-00', '00:00:00', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `ID_USER` int(10) UNSIGNED NOT NULL,
  `GUID` varchar(20) NOT NULL,
  `USERNAME` varchar(20) NOT NULL,
  `PASSWORD` varchar(255) NOT NULL,
  `CREATED_AT` date NOT NULL,
  `STATUS` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`ID_USER`, `GUID`, `USERNAME`, `PASSWORD`, `CREATED_AT`, `STATUS`) VALUES
(1, '5acff05a49592', 'admin', '$2y$10$tAXZMRMEQhx8OqmODGBEfOp4SCgBDm//WNRBGZaRBY2Jf1BuUdHrC', '2018-01-01', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`ID_USER`,`ID_QUOTE`),
  ADD KEY `fk_LIKES_QUOTES1_idx` (`ID_QUOTE`);

--
-- Indexes for table `quotes`
--
ALTER TABLE `quotes`
  ADD PRIMARY KEY (`ID_QUOTE`),
  ADD UNIQUE KEY `ID_QUOTE_UNIQUE` (`ID_QUOTE`),
  ADD KEY `fk_QUOTES_USERS_idx` (`ID_USER`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID_USER`),
  ADD UNIQUE KEY `ID_USER_UNIQUE` (`ID_USER`),
  ADD UNIQUE KEY `USER_UNIQUE` (`USERNAME`),
  ADD UNIQUE KEY `GUID_UNIQUE` (`GUID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `quotes`
--
ALTER TABLE `quotes`
  MODIFY `ID_QUOTE` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID_USER` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `fk_LIKES_QUOTES1` FOREIGN KEY (`ID_QUOTE`) REFERENCES `quotes` (`ID_QUOTE`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_LIKES_USERS1` FOREIGN KEY (`ID_USER`) REFERENCES `users` (`ID_USER`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `quotes`
--
ALTER TABLE `quotes`
  ADD CONSTRAINT `fk_QUOTES_USERS` FOREIGN KEY (`ID_USER`) REFERENCES `users` (`ID_USER`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;