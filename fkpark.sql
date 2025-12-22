-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Dec 22, 2025 at 08:59 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fkpark`
--

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `BookingID` varchar(10) NOT NULL,
  `ParkingSpaceID` varchar(10) NOT NULL,
  `BookingDate` date DEFAULT NULL,
  `StartTime` time DEFAULT NULL,
  `EndTime` time DEFAULT NULL,
  `Status` varchar(20) DEFAULT NULL,
  `CreatedAt` datetime DEFAULT NULL,
  `UserID` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`BookingID`, `ParkingSpaceID`, `BookingDate`, `StartTime`, `EndTime`, `Status`, `CreatedAt`, `UserID`) VALUES
('BK001', 'PS002', '2025-12-09', '14:26:00', '16:29:00', 'Completed', '2025-12-09 14:26:39', 'CB23045'),
('BK002', 'PS003', '2025-12-09', '20:26:00', '21:27:00', 'Cancelled', '2025-12-09 14:27:10', 'CB23045');

-- --------------------------------------------------------

--
-- Table structure for table `bookingqrcode`
--

CREATE TABLE `bookingqrcode` (
  `QRCodeID` int(11) NOT NULL,
  `BookingID` varchar(10) NOT NULL,
  `QRCodeData` text DEFAULT NULL,
  `GeneratedDate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookingqrcode`
--

INSERT INTO `bookingqrcode` (`QRCodeID`, `BookingID`, `QRCodeData`, `GeneratedDate`) VALUES
(0, 'BK001', 'BOOK-BK001-79a8a087', '2025-12-09 14:26:39');

-- --------------------------------------------------------

--
-- Table structure for table `Demerit`
--

CREATE TABLE `Demerit` (
  `DemeritID` int(11) NOT NULL,
  `SummonID` int(11) NOT NULL,
  `DemeritPoints` int(11) NOT NULL,
  `IssuedDate` date DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `Status` varchar(15) DEFAULT 'Active',
  `UserID` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Demerit`
--

INSERT INTO `Demerit` (`DemeritID`, `SummonID`, `DemeritPoints`, `IssuedDate`, `Description`, `Status`, `UserID`) VALUES
(1, 12, 10, '2025-12-22', NULL, 'Active', NULL),
(2, 13, 10, '2025-12-22', NULL, 'Active', NULL),
(3, 14, 10, '2025-12-22', NULL, 'Active', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `parkinglog`
--

CREATE TABLE `parkinglog` (
  `LogID` varchar(10) NOT NULL,
  `BookingID` varchar(10) NOT NULL,
  `CheckInTime` datetime DEFAULT NULL,
  `ExpectedDuration` time DEFAULT NULL,
  `CheckOutTime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parkinglog`
--

INSERT INTO `parkinglog` (`LogID`, `BookingID`, `CheckInTime`, `ExpectedDuration`, `CheckOutTime`) VALUES
('LG001', 'BK001', '2025-12-09 07:29:13', '00:00:15', '2025-12-09 07:30:46');

-- --------------------------------------------------------

--
-- Table structure for table `parking_area`
--

CREATE TABLE `parking_area` (
  `ParkingAreaID` varchar(10) NOT NULL,
  `AreaCode` varchar(20) DEFAULT NULL,
  `AreaName` varchar(50) DEFAULT NULL,
  `AreaType` varchar(20) DEFAULT NULL,
  `AreaDescription` text DEFAULT NULL,
  `AreaStatus` varchar(20) NOT NULL DEFAULT 'Active',
  `Capacity` int(11) DEFAULT NULL,
  `LocationDesc` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parking_area`
--

INSERT INTO `parking_area` (`ParkingAreaID`, `AreaCode`, `AreaName`, `AreaType`, `AreaDescription`, `AreaStatus`, `Capacity`, `LocationDesc`) VALUES
('PA01', 'A1', 'Student Parking Zone A', 'Student', 'Main student parking area', 'Closed', 50, 'Near Block A'),
('PA02', 'B1', 'Student Parking Zone B', 'Student', 'Overflow student parking', 'Active', 40, 'Near Block B'),
('PA03', 'C1', 'Staff Parking Zone C', 'Staff', 'Reserved staff parking', 'Active', 30, 'Near Admin Building'),
('PA406', 'F', 'Student Parking D', 'Student', 'ghjgjh', 'Inactive', 10, 'bhhjgjghj');

-- --------------------------------------------------------

--
-- Table structure for table `parking_space`
--

CREATE TABLE `parking_space` (
  `ParkingSpaceID` varchar(10) NOT NULL,
  `ParkingAreaID` varchar(10) NOT NULL,
  `StatusID` varchar(10) DEFAULT NULL,
  `SpaceCode` varchar(20) DEFAULT NULL,
  `SpaceType` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parking_space`
--

INSERT INTO `parking_space` (`ParkingSpaceID`, `ParkingAreaID`, `StatusID`, `SpaceCode`, `SpaceType`) VALUES
('PS001', 'PA01', 'ST02', 'A-01', 'Car'),
('PS002', 'PA01', 'ST01', 'A-02', 'Car'),
('PS003', 'PA01', 'ST01', 'A-03', 'Car'),
('PS004', 'PA01', 'ST01', 'A-04', 'Car'),
('PS005', 'PA01', 'ST01', 'A-05', 'Car'),
('PS462', 'PA03', 'ST03', 'C-003', 'Car'),
('PS568', 'PA01', 'ST01', 'B-003', 'Car'),
('PS814', 'PA02', 'ST03', 'B-003', 'Car');

-- --------------------------------------------------------

--
-- Table structure for table `PunishmentDuration`
--

CREATE TABLE `PunishmentDuration` (
  `PunishmentDurationID` int(11) NOT NULL,
  `PunishmentType` varchar(50) DEFAULT NULL,
  `StartDate` date DEFAULT NULL,
  `EndDate` date DEFAULT NULL,
  `Status` varchar(50) DEFAULT 'Active',
  `UserID` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `PunishmentDuration`
--

INSERT INTO `PunishmentDuration` (`PunishmentDurationID`, `PunishmentType`, `StartDate`, `EndDate`, `Status`, `UserID`) VALUES
(1, 'Vehicle Revoked (1 Semester)', '2025-12-22', '2026-06-22', 'Active', 'CB23045');

-- --------------------------------------------------------

--
-- Table structure for table `securitystaff`
--

CREATE TABLE `securitystaff` (
  `UserID` varchar(10) NOT NULL,
  `Department` varchar(50) DEFAULT NULL,
  `BadgeNumber` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `securitystaff`
--

INSERT INTO `securitystaff` (`UserID`, `Department`, `BadgeNumber`) VALUES
('SS001', 'Safety Management Unit', 'SMU-001');

-- --------------------------------------------------------

--
-- Table structure for table `space_qr_code`
--

CREATE TABLE `space_qr_code` (
  `QRCodeID` int(11) NOT NULL,
  `ParkingSpaceID` varchar(10) NOT NULL,
  `QRCodeData` text DEFAULT NULL,
  `QRImage` varchar(255) DEFAULT NULL,
  `GeneratedDate` datetime DEFAULT NULL,
  `GeneratedBy` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `space_qr_code`
--

INSERT INTO `space_qr_code` (`QRCodeID`, `ParkingSpaceID`, `QRCodeData`, `QRImage`, `GeneratedDate`, `GeneratedBy`) VALUES
(1, 'PS001', 'QR-PS001-0498fa', NULL, '2025-12-09 15:20:52', 'A001'),
(2, 'PS002', 'BOOK-SP-PS002-a2aed38d', NULL, '2025-12-12 07:51:47', 'A001'),
(3, 'PS814', 'BOOK-SP-PS814-43f06718', NULL, '2025-12-12 07:57:25', 'A001'),
(4, 'PS001', 'BOOK-SP-PS001-da8ecdfe', 'qr_PS001_1765600280.png', '2025-12-13 05:31:20', 'A001'),
(5, 'PS002', 'BOOK-SP-PS002-1290b837', 'qr_PS002_1765600324.png', '2025-12-13 05:32:04', 'A001');

-- --------------------------------------------------------

--
-- Table structure for table `space_status`
--

CREATE TABLE `space_status` (
  `StatusID` varchar(10) NOT NULL,
  `StatusName` varchar(50) DEFAULT NULL,
  `Description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `space_status`
--

INSERT INTO `space_status` (`StatusID`, `StatusName`, `Description`) VALUES
('ST01', 'Available', 'Space is free'),
('ST02', 'Occupied', 'Space in use'),
('ST03', 'Reserved', 'Reserved for booking'),
('ST04', 'Blocked', 'Temporarily unavailable');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `UserID` varchar(10) NOT NULL,
  `StudentYear` varchar(10) DEFAULT NULL,
  `StudentProgram` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`UserID`, `StudentYear`, `StudentProgram`) VALUES
('CB23045', 'Year 2', 'Software Engineering');

-- --------------------------------------------------------

--
-- Table structure for table `Summon`
--

CREATE TABLE `Summon` (
  `SummonID` int(11) NOT NULL,
  `VehicleID` varchar(10) NOT NULL,
  `ViolationTypeID` varchar(10) NOT NULL,
  `SummonDate` date DEFAULT NULL,
  `SummonTime` time DEFAULT NULL,
  `Location` varchar(50) DEFAULT NULL,
  `Evidence` text DEFAULT NULL,
  `SummonStatus` varchar(15) DEFAULT 'Issued',
  `UserID` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Summon`
--

INSERT INTO `Summon` (`SummonID`, `VehicleID`, `ViolationTypeID`, `SummonDate`, `SummonTime`, `Location`, `Evidence`, `SummonStatus`, `UserID`) VALUES
(11, 'V001', 'VT001', '2025-12-22', '15:49:00', 'FK Parking A', 'http://localhost/FKPark/uploads/many-cake-slices.jpg', 'Unpaid', NULL),
(12, 'V001', 'VT001', '2025-12-22', '15:49:00', 'FK Parking A', 'http://localhost/FKPark/uploads/many-cake-slices.jpg', 'Unpaid', NULL),
(13, 'V001', 'VT001', '2025-12-22', '15:51:00', 'FK Parking A', 'http://localhost/FKPark/uploads/many-cake-slices.jpg', 'Unpaid', NULL),
(14, 'V001', 'VT001', '2025-12-22', '15:51:00', 'FK Parking A', 'http://localhost/FKPark/uploads/many-cake-slices.jpg', 'Unpaid', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `SummonQRCode`
--

CREATE TABLE `SummonQRCode` (
  `QRCodeID` int(11) NOT NULL,
  `SummonID` int(11) NOT NULL,
  `QRCodeData` text DEFAULT NULL,
  `GenerateDate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `SummonQRCode`
--

INSERT INTO `SummonQRCode` (`QRCodeID`, `SummonID`, `QRCodeData`, `GenerateDate`) VALUES
(6, 12, '../Module4/qrcodes/summon_12.png', '2025-12-22 15:50:49'),
(7, 14, '../Module4/qrcodes/summon_14.png', '2025-12-22 15:52:14');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `UserID` varchar(10) NOT NULL,
  `UserName` varchar(100) NOT NULL,
  `UserEmail` varchar(100) NOT NULL,
  `UserPassword` varchar(255) NOT NULL,
  `UserRole` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`UserID`, `UserName`, `UserEmail`, `UserPassword`, `UserRole`) VALUES
('A001', 'System Administrator', 'admin@fkpark.edu.my', '$2y$10$0rHR.fGC1FScG5qJDtYOy..TTZgDcuamu8JX2eRA6LTTuTl/wrNCi', 'Administrator'),
('CB23045', 'Ahmad Danial bin Razak', 'CB23045@adab.umpsa.edu.my', '$2y$10$xjeh17xSs3oRL5Warf5exeTGCwpUBvJEtZ3evoupgo2Bz3UCxGcPS', 'Student'),
('SS001', 'Mohd Rizal bin Ahmad', 'staff001@umpsa.edu.my', '$2y$10$iqYYfaLG7MARa18d/2/cQeaOOm6dhPqjgVpCO8sA5kRHy3DoG/E9a', 'Security Staff');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle`
--

CREATE TABLE `vehicle` (
  `VehicleID` varchar(10) NOT NULL,
  `PlateNumber` varchar(15) NOT NULL,
  `VehicleType` varchar(15) DEFAULT NULL,
  `VehicleGrant` varchar(100) DEFAULT NULL,
  `ApprovalStatus` varchar(15) DEFAULT 'Pending',
  `UserID` varchar(10) DEFAULT NULL,
  `ApprovedBy` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle`
--

INSERT INTO `vehicle` (`VehicleID`, `PlateNumber`, `VehicleType`, `VehicleGrant`, `ApprovalStatus`, `UserID`) VALUES
('V001', 'ABC1234', 'Car', '../uploads/vehicle_grants/Screenshot 2025-12-09 135117.png', 'Approved', 'CB23045');

-- --------------------------------------------------------

--
-- Table structure for table `violationtype`
--

CREATE TABLE `violationtype` (
  `ViolationTypeID` varchar(10) NOT NULL,
  `ViolationName` varchar(50) DEFAULT NULL,
  `ViolationPoints` int(11) DEFAULT NULL,
  `Description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `violationtype`
--

INSERT INTO `violationtype` (`ViolationTypeID`, `ViolationName`, `ViolationPoints`, `Description`) VALUES
('VT001', 'Parking Violation', 10, 'Parking at unauthorized area'),
('VT002', 'Not Comply Traffic Regulations', 15, 'Failure to follow campus traffic rules'),
('VT003', 'Accident Caused', 20, 'Causing accident within campus');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`BookingID`),
  ADD KEY `ParkingSpaceID` (`ParkingSpaceID`),
  ADD KEY `fk_booking_user` (`UserID`);

--
-- Indexes for table `bookingqrcode`
--
ALTER TABLE `bookingqrcode`
  ADD PRIMARY KEY (`QRCodeID`),
  ADD KEY `BookingID` (`BookingID`);

--
-- Indexes for table `Demerit`
--
ALTER TABLE `Demerit`
  ADD PRIMARY KEY (`DemeritID`),
  ADD KEY `SummonID` (`SummonID`),
  ADD KEY `fk_demerit_user` (`UserID`);

--
-- Indexes for table `parkinglog`
--
ALTER TABLE `parkinglog`
  ADD PRIMARY KEY (`LogID`),
  ADD KEY `BookingID` (`BookingID`);

--
-- Indexes for table `parking_area`
--
ALTER TABLE `parking_area`
  ADD PRIMARY KEY (`ParkingAreaID`);

--
-- Indexes for table `parking_space`
--
ALTER TABLE `parking_space`
  ADD PRIMARY KEY (`ParkingSpaceID`),
  ADD KEY `ParkingAreaID` (`ParkingAreaID`),
  ADD KEY `StatusID` (`StatusID`);

--
-- Indexes for table `PunishmentDuration`
--
ALTER TABLE `PunishmentDuration`
  ADD PRIMARY KEY (`PunishmentDurationID`),
  ADD KEY `fk_duration_user` (`UserID`);

--
-- Indexes for table `securitystaff`
--
ALTER TABLE `securitystaff`
  ADD PRIMARY KEY (`UserID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `space_qr_code`
--
ALTER TABLE `space_qr_code`
  ADD PRIMARY KEY (`QRCodeID`),
  ADD KEY `ParkingSpaceID` (`ParkingSpaceID`),
  ADD KEY `GeneratedBy` (`GeneratedBy`);

--
-- Indexes for table `space_status`
--
ALTER TABLE `space_status`
  ADD PRIMARY KEY (`StatusID`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`UserID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `Summon`
--
ALTER TABLE `Summon`
  ADD PRIMARY KEY (`SummonID`),
  ADD KEY `VehicleID` (`VehicleID`),
  ADD KEY `ViolationTypeID` (`ViolationTypeID`),
  ADD KEY `fk_summon_user` (`UserID`);

--
-- Indexes for table `SummonQRCode`
--
ALTER TABLE `SummonQRCode`
  ADD PRIMARY KEY (`QRCodeID`),
  ADD KEY `SummonID` (`SummonID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UserID`);

--
-- Indexes for table `vehicle`
--
ALTER TABLE `vehicle`
  ADD PRIMARY KEY (`VehicleID`),
  ADD KEY `fk_vehicle_user` (`UserID`),
  ADD KEY `fk_vehicle_approvedby` (`ApprovedBy`);

--
-- Indexes for table `violationtype`
--
ALTER TABLE `violationtype`
  ADD PRIMARY KEY (`ViolationTypeID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Demerit`
--
ALTER TABLE `Demerit`
  MODIFY `DemeritID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `PunishmentDuration`
--
ALTER TABLE `PunishmentDuration`
  MODIFY `PunishmentDurationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `space_qr_code`
--
ALTER TABLE `space_qr_code`
  MODIFY `QRCodeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `Summon`
--
ALTER TABLE `Summon`
  MODIFY `SummonID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `SummonQRCode`
--
ALTER TABLE `SummonQRCode`
  MODIFY `QRCodeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`ParkingSpaceID`) REFERENCES `parking_space` (`ParkingSpaceID`),
  ADD CONSTRAINT `fk_booking_user` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`);

--
-- Constraints for table `bookingqrcode`
--
ALTER TABLE `bookingqrcode`
  ADD CONSTRAINT `bookingqrcode_ibfk_1` FOREIGN KEY (`BookingID`) REFERENCES `booking` (`BookingID`);

--
-- Constraints for table `Demerit`
--
ALTER TABLE `Demerit`
  ADD CONSTRAINT `fk_demerit_summon` FOREIGN KEY (`SummonID`) REFERENCES `Summon` (`SummonID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `parkinglog`
--
ALTER TABLE `parkinglog`
  ADD CONSTRAINT `parkinglog_ibfk_1` FOREIGN KEY (`BookingID`) REFERENCES `booking` (`BookingID`);

--
-- Constraints for table `parking_space`
--
ALTER TABLE `parking_space`
  ADD CONSTRAINT `parking_space_ibfk_1` FOREIGN KEY (`ParkingAreaID`) REFERENCES `parking_area` (`ParkingAreaID`),
  ADD CONSTRAINT `parking_space_ibfk_2` FOREIGN KEY (`StatusID`) REFERENCES `space_status` (`StatusID`);

--
-- Constraints for table `PunishmentDuration`
--
ALTER TABLE `PunishmentDuration`
  ADD CONSTRAINT `fk_duration_user` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`),
  ADD CONSTRAINT `fk_punishment_user` FOREIGN KEY (`UserID`) REFERENCES `User` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `securitystaff`
--
ALTER TABLE `securitystaff`
  ADD CONSTRAINT `fk_securitystaff_user` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`),
  ADD CONSTRAINT `securitystaff_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`);

--
-- Constraints for table `space_qr_code`
--
ALTER TABLE `space_qr_code`
  ADD CONSTRAINT `space_qr_code_ibfk_1` FOREIGN KEY (`ParkingSpaceID`) REFERENCES `parking_space` (`ParkingSpaceID`),
  ADD CONSTRAINT `space_qr_code_ibfk_2` FOREIGN KEY (`GeneratedBy`) REFERENCES `user` (`UserID`);

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`);

--
-- Constraints for table `Summon`
--
ALTER TABLE `Summon`
  ADD CONSTRAINT `fk_summon_user` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`),
  ADD CONSTRAINT `fk_summon_vehicle` FOREIGN KEY (`VehicleID`) REFERENCES `Vehicle` (`VehicleID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_summon_vehicle_fix` FOREIGN KEY (`VehicleID`) REFERENCES `Vehicle` (`VehicleID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `summon_ibfk_1` FOREIGN KEY (`VehicleID`) REFERENCES `vehicle` (`VehicleID`),
  ADD CONSTRAINT `summon_ibfk_3` FOREIGN KEY (`ViolationTypeID`) REFERENCES `violationtype` (`ViolationTypeID`);

--
-- Constraints for table `SummonQRCode`
--
ALTER TABLE `SummonQRCode`
  ADD CONSTRAINT `fk_qrcode_summon` FOREIGN KEY (`SummonID`) REFERENCES `Summon` (`SummonID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `summonqrcode_ibfk_1` FOREIGN KEY (`SummonID`) REFERENCES `Summon` (`SummonID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `vehicle`
--
ALTER TABLE `vehicle`
  ADD CONSTRAINT `fk_vehicle_approvedby` FOREIGN KEY (`ApprovedBy`) REFERENCES `user` (`UserID`),
  ADD CONSTRAINT `fk_vehicle_user` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
