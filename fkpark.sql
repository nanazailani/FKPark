-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Dec 10, 2025 at 02:00 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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
  `StudentID` varchar(10) NOT NULL,
  `ParkingSpaceID` varchar(10) NOT NULL,
  `BookingDate` date DEFAULT NULL,
  `StartTime` time DEFAULT NULL,
  `EndTime` time DEFAULT NULL,
  `Status` varchar(20) DEFAULT NULL,
  `CreatedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`BookingID`, `StudentID`, `ParkingSpaceID`, `BookingDate`, `StartTime`, `EndTime`, `Status`, `CreatedAt`) VALUES
('BK001', 'CB23045', 'PS002', '2025-12-09', '14:26:00', '16:29:00', 'Completed', '2025-12-09 14:26:39'),
('BK002', 'CB23045', 'PS003', '2025-12-09', '20:26:00', '21:27:00', 'Cancelled', '2025-12-09 14:27:10');

-- --------------------------------------------------------

--
-- Table structure for table `bookingqrcode`
--

CREATE TABLE `bookingqrcode` (
  `QRCodeID` int(11) NOT NULL,
  `BookingID` varchar(10) NOT NULL,
  `QRCodeData` text DEFAULT NULL,
  `GeneratedDate` datetime DEFAULT NULL,
  `GeneratedBy` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookingqrcode`
--

INSERT INTO `bookingqrcode` (`QRCodeID`, `BookingID`, `QRCodeData`, `GeneratedDate`, `GeneratedBy`) VALUES
(0, 'BK001', 'BOOK-BK001-79a8a087', '2025-12-09 14:26:39', 'CB23045');

-- --------------------------------------------------------

--
-- Table structure for table `demerit`
--

CREATE TABLE `demerit` (
  `DemeritID` int(11) NOT NULL,
  `StudentID` varchar(10) NOT NULL,
  `SummonID` int(11) NOT NULL,
  `DemeritPoints` int(11) NOT NULL,
  `IssuedDate` date DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `Status` varchar(15) DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parkinglog`
--

CREATE TABLE `parkinglog` (
  `LogID` varchar(10) NOT NULL,
  `BookingID` varchar(10) NOT NULL,
  `StudentID` varchar(10) NOT NULL,
  `ParkingSpaceID` varchar(10) NOT NULL,
  `CheckInTime` datetime DEFAULT NULL,
  `ExpectedDuration` time DEFAULT NULL,
  `CheckOutTime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parkinglog`
--

INSERT INTO `parkinglog` (`LogID`, `BookingID`, `StudentID`, `ParkingSpaceID`, `CheckInTime`, `ExpectedDuration`, `CheckOutTime`) VALUES
('LG001', 'BK001', 'CB23045', 'PS002', '2025-12-09 07:29:13', '00:00:15', '2025-12-09 07:30:46');

-- --------------------------------------------------------

--
-- Table structure for table `parking_area`
--

CREATE TABLE `parking_area` (
  `ParkingAreaID` varchar(10) NOT NULL,
  `StatusID` varchar(10) DEFAULT NULL,
  `AreaCode` varchar(20) DEFAULT NULL,
  `AreaName` varchar(50) DEFAULT NULL,
  `AreaType` varchar(20) DEFAULT NULL,
  `AreaDescription` text DEFAULT NULL,
  `Capacity` int(11) DEFAULT NULL,
  `LocationDesc` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parking_area`
--

INSERT INTO `parking_area` (`ParkingAreaID`, `StatusID`, `AreaCode`, `AreaName`, `AreaType`, `AreaDescription`, `Capacity`, `LocationDesc`) VALUES
('PA01', 'ST01', 'A1', 'Student Parking Zone A', 'Student', 'Main student parking area', 50, 'Near Block A'),
('PA02', 'ST01', 'B1', 'Student Parking Zone B', 'Student', 'Overflow student parking', 40, 'Near Block B'),
('PA03', 'ST01', 'C1', 'Staff Parking Zone C', 'Staff', 'Reserved staff parking', 30, 'Near Admin Building'),
('PA343', 'ST02', 'D', 'Staff Parking D', 'Staff', 'staff only', 10, 'Near FK West Wing');

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
('PS462', 'PA03', 'ST03', 'C-003', 'Car');

-- --------------------------------------------------------

--
-- Table structure for table `punishmentduration`
--

CREATE TABLE `punishmentduration` (
  `PunishmentDurationID` int(11) NOT NULL,
  `StudentID` varchar(10) NOT NULL,
  `PunishmentType` varchar(50) DEFAULT NULL,
  `StartDate` date DEFAULT NULL,
  `EndDate` date DEFAULT NULL,
  `Status` varchar(50) DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `securitystaff`
--

CREATE TABLE `securitystaff` (
  `SStaffID` varchar(10) NOT NULL,
  `UserID` varchar(10) DEFAULT NULL,
  `Department` varchar(50) DEFAULT NULL,
  `BadgeNumber` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `securitystaff`
--

INSERT INTO `securitystaff` (`SStaffID`, `UserID`, `Department`, `BadgeNumber`) VALUES
('SS001', 'SS001', 'Safety Management Unit', 'SMU-001');

-- --------------------------------------------------------

--
-- Table structure for table `space_qr_code`
--

CREATE TABLE `space_qr_code` (
  `QRCodeID` int(11) NOT NULL,
  `ParkingSpaceID` varchar(10) NOT NULL,
  `QRCodeData` text DEFAULT NULL,
  `GeneratedDate` datetime DEFAULT NULL,
  `GeneratedBy` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `space_qr_code`
--

INSERT INTO `space_qr_code` (`QRCodeID`, `ParkingSpaceID`, `QRCodeData`, `GeneratedDate`, `GeneratedBy`) VALUES
(1, 'PS001', 'QR-PS001-0498fa', '2025-12-09 15:20:52', 'A001');

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
  `StudentID` varchar(10) NOT NULL,
  `UserID` varchar(10) DEFAULT NULL,
  `StudentYear` varchar(10) DEFAULT NULL,
  `StudentProgram` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`StudentID`, `UserID`, `StudentYear`, `StudentProgram`) VALUES
('CB23045', 'CB23045', 'Year 2', 'Software Engineering');

-- --------------------------------------------------------

--
-- Table structure for table `summon`
--

CREATE TABLE `summon` (
  `SummonID` int(11) NOT NULL,
  `VehicleID` varchar(10) NOT NULL,
  `SStaffID` varchar(10) DEFAULT NULL,
  `ViolationTypeID` varchar(10) NOT NULL,
  `SummonDate` date DEFAULT NULL,
  `SummonTime` time DEFAULT NULL,
  `Location` varchar(50) DEFAULT NULL,
  `Evidence` text DEFAULT NULL,
  `SummonStatus` varchar(15) DEFAULT 'Issued'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `summonqrcode`
--

CREATE TABLE `summonqrcode` (
  `QRCodeID` int(11) NOT NULL,
  `SummonID` int(11) NOT NULL,
  `QRCodeData` text DEFAULT NULL,
  `GenerateDate` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
('A001', 'System Administrator', 'admin@fkpark.edu.my', '$2y$12$spAnsIMSlhwJU9tU9/Sd1eymbFra5qBaRHMhZ2mt9vDWYk3eLy7xG', 'Administrator'),
('CB23045', 'Ahmad Danial bin Razak', 'CB23045@adab.umpsa.edu.my', '$2y$12$1JRZs.vgDlJm0WD3NQmdFudlre9S8xW.Zh1PgAJ9nOQrmaWJGhIkC', 'Student'),
('SS001', 'Mohd Rizal bin Ahmad', 'staff001@umpsa.edu.my', '$2y$12$CK2acAvowJPmTpTXwR8v5./Vuvvj6KVlDUlZXb8xc/zz94k5A06eG', 'Security Staff');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle`
--

CREATE TABLE `vehicle` (
  `VehicleID` varchar(10) NOT NULL,
  `StudentID` varchar(10) DEFAULT NULL,
  `SStaffID` varchar(10) DEFAULT NULL,
  `PlateNumber` varchar(15) NOT NULL,
  `VehicleType` varchar(15) DEFAULT NULL,
  `VehicleGrant` varchar(100) DEFAULT NULL,
  `ApprovalStatus` varchar(15) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle`
--

INSERT INTO `vehicle` (`VehicleID`, `StudentID`, `SStaffID`, `PlateNumber`, `VehicleType`, `VehicleGrant`, `ApprovalStatus`) VALUES
('V001', 'CB23045', NULL, 'ABC 1234', 'Car', '../uploads/vehicle_grants/Screenshot 2025-12-09 135117.png', 'Approved');

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
  ADD KEY `StudentID` (`StudentID`),
  ADD KEY `ParkingSpaceID` (`ParkingSpaceID`);

--
-- Indexes for table `bookingqrcode`
--
ALTER TABLE `bookingqrcode`
  ADD PRIMARY KEY (`QRCodeID`),
  ADD KEY `BookingID` (`BookingID`),
  ADD KEY `GeneratedBy` (`GeneratedBy`);

--
-- Indexes for table `demerit`
--
ALTER TABLE `demerit`
  ADD PRIMARY KEY (`DemeritID`),
  ADD KEY `StudentID` (`StudentID`),
  ADD KEY `SummonID` (`SummonID`);

--
-- Indexes for table `parkinglog`
--
ALTER TABLE `parkinglog`
  ADD PRIMARY KEY (`LogID`),
  ADD KEY `BookingID` (`BookingID`),
  ADD KEY `StudentID` (`StudentID`),
  ADD KEY `ParkingSpaceID` (`ParkingSpaceID`);

--
-- Indexes for table `parking_area`
--
ALTER TABLE `parking_area`
  ADD PRIMARY KEY (`ParkingAreaID`),
  ADD KEY `StatusID` (`StatusID`);

--
-- Indexes for table `parking_space`
--
ALTER TABLE `parking_space`
  ADD PRIMARY KEY (`ParkingSpaceID`),
  ADD KEY `ParkingAreaID` (`ParkingAreaID`),
  ADD KEY `StatusID` (`StatusID`);

--
-- Indexes for table `punishmentduration`
--
ALTER TABLE `punishmentduration`
  ADD PRIMARY KEY (`PunishmentDurationID`),
  ADD KEY `StudentID` (`StudentID`);

--
-- Indexes for table `securitystaff`
--
ALTER TABLE `securitystaff`
  ADD PRIMARY KEY (`SStaffID`),
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
  ADD PRIMARY KEY (`StudentID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `summon`
--
ALTER TABLE `summon`
  ADD PRIMARY KEY (`SummonID`),
  ADD KEY `VehicleID` (`VehicleID`),
  ADD KEY `SStaffID` (`SStaffID`),
  ADD KEY `ViolationTypeID` (`ViolationTypeID`);

--
-- Indexes for table `summonqrcode`
--
ALTER TABLE `summonqrcode`
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
  ADD KEY `StudentID` (`StudentID`),
  ADD KEY `SStaffID` (`SStaffID`);

--
-- Indexes for table `violationtype`
--
ALTER TABLE `violationtype`
  ADD PRIMARY KEY (`ViolationTypeID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `space_qr_code`
--
ALTER TABLE `space_qr_code`
  MODIFY `QRCodeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`StudentID`) REFERENCES `student` (`StudentID`),
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`ParkingSpaceID`) REFERENCES `parking_space` (`ParkingSpaceID`);

--
-- Constraints for table `bookingqrcode`
--
ALTER TABLE `bookingqrcode`
  ADD CONSTRAINT `bookingqrcode_ibfk_1` FOREIGN KEY (`BookingID`) REFERENCES `booking` (`BookingID`),
  ADD CONSTRAINT `bookingqrcode_ibfk_2` FOREIGN KEY (`GeneratedBy`) REFERENCES `user` (`UserID`);

--
-- Constraints for table `demerit`
--
ALTER TABLE `demerit`
  ADD CONSTRAINT `demerit_ibfk_1` FOREIGN KEY (`StudentID`) REFERENCES `student` (`StudentID`),
  ADD CONSTRAINT `demerit_ibfk_2` FOREIGN KEY (`SummonID`) REFERENCES `summon` (`SummonID`);

--
-- Constraints for table `parkinglog`
--
ALTER TABLE `parkinglog`
  ADD CONSTRAINT `parkinglog_ibfk_1` FOREIGN KEY (`BookingID`) REFERENCES `booking` (`BookingID`),
  ADD CONSTRAINT `parkinglog_ibfk_2` FOREIGN KEY (`StudentID`) REFERENCES `student` (`StudentID`),
  ADD CONSTRAINT `parkinglog_ibfk_3` FOREIGN KEY (`ParkingSpaceID`) REFERENCES `parking_space` (`ParkingSpaceID`);

--
-- Constraints for table `parking_area`
--
ALTER TABLE `parking_area`
  ADD CONSTRAINT `parking_area_ibfk_1` FOREIGN KEY (`StatusID`) REFERENCES `space_status` (`StatusID`);

--
-- Constraints for table `parking_space`
--
ALTER TABLE `parking_space`
  ADD CONSTRAINT `parking_space_ibfk_1` FOREIGN KEY (`ParkingAreaID`) REFERENCES `parking_area` (`ParkingAreaID`),
  ADD CONSTRAINT `parking_space_ibfk_2` FOREIGN KEY (`StatusID`) REFERENCES `space_status` (`StatusID`);

--
-- Constraints for table `punishmentduration`
--
ALTER TABLE `punishmentduration`
  ADD CONSTRAINT `punishmentduration_ibfk_1` FOREIGN KEY (`StudentID`) REFERENCES `student` (`StudentID`);

--
-- Constraints for table `securitystaff`
--
ALTER TABLE `securitystaff`
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
-- Constraints for table `summon`
--
ALTER TABLE `summon`
  ADD CONSTRAINT `summon_ibfk_1` FOREIGN KEY (`VehicleID`) REFERENCES `vehicle` (`VehicleID`),
  ADD CONSTRAINT `summon_ibfk_2` FOREIGN KEY (`SStaffID`) REFERENCES `securitystaff` (`SStaffID`),
  ADD CONSTRAINT `summon_ibfk_3` FOREIGN KEY (`ViolationTypeID`) REFERENCES `violationtype` (`ViolationTypeID`);

--
-- Constraints for table `summonqrcode`
--
ALTER TABLE `summonqrcode`
  ADD CONSTRAINT `summonqrcode_ibfk_1` FOREIGN KEY (`SummonID`) REFERENCES `summon` (`SummonID`);

--
-- Constraints for table `vehicle`
--
ALTER TABLE `vehicle`
  ADD CONSTRAINT `vehicle_ibfk_1` FOREIGN KEY (`StudentID`) REFERENCES `student` (`StudentID`),
  ADD CONSTRAINT `vehicle_ibfk_2` FOREIGN KEY (`SStaffID`) REFERENCES `securitystaff` (`SStaffID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
