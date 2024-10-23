-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 17, 2024 at 06:05 AM
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
-- Database: `iwanderph_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `AdminID` int(3) NOT NULL,
  `Name` varchar(30) NOT NULL,
  `Username` varchar(30) NOT NULL,
  `Password` varchar(30) NOT NULL,
  `AdminUserType` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`AdminID`, `Name`, `Username`, `Password`, `AdminUserType`) VALUES
(13, 'Kim Pop', 'kimashi', 'kimashi', 'SuperAdmin');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `BookingID` int(10) NOT NULL,
  `BookingDate` datetime DEFAULT current_timestamp(),
  `PaymentStatus` varchar(20) NOT NULL,
  `ListingID` int(8) NOT NULL,
  `BookingStatus` varchar(20) NOT NULL,
  `Duration` varchar(2) NOT NULL,
  `CheckIn` datetime DEFAULT NULL,
  `CheckOut` datetime DEFAULT NULL,
  `Subtotal` decimal(10,2) NOT NULL,
  `VAT` decimal(10,2) NOT NULL,
  `PayoutAmount` decimal(10,2) NOT NULL,
  `TotalAmount` decimal(10,2) NOT NULL,
  `RefundAmount` decimal(10,2) NOT NULL,
  `MerchantID` int(3) NOT NULL,
  `TravelerID` int(5) NOT NULL,
  `ReasonForRefund` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`BookingID`, `BookingDate`, `PaymentStatus`, `ListingID`, `BookingStatus`, `Duration`, `CheckIn`, `CheckOut`, `Subtotal`, `VAT`, `PayoutAmount`, `TotalAmount`, `RefundAmount`, `MerchantID`, `TravelerID`, `ReasonForRefund`) VALUES
(1000001, '2024-01-10 14:23:00', 'successful', 1000201, 'canceled', '3', '2024-02-01 15:00:00', '2024-02-04 12:00:00', 15000.00, 1800.00, 1200.00, 16800.00, 0.00, 102, 10015, NULL),
(1000002, '2024-01-12 09:45:00', 'failed', 1000202, 'Completed', '5', '2024-03-10 15:00:00', '2024-03-15 12:00:00', 12500.00, 1500.00, 1000.00, 14000.00, 0.00, 102, 10018, NULL),
(1000003, '2024-01-15 16:30:00', 'in-progress', 1000203, 'cancelled', '2', '2024-04-20 15:00:00', '2024-04-22 12:00:00', 20000.00, 2400.00, 1600.00, 22400.00, 0.00, 102, 10022, NULL),
(1000004, '2024-01-20 11:00:00', 'successful', 1000204, 'Checked-In', '1', '2024-05-05 15:00:00', '2024-05-06 12:00:00', 5000.00, 600.00, 400.00, 5600.00, 0.00, 103, 10015, NULL),
(1000005, '2024-01-22 14:30:00', 'in-progress', 1000205, 'Completed', '4', '2024-05-20 15:00:00', '2024-05-24 12:00:00', 18000.00, 2160.00, 1440.00, 20160.00, 0.00, 103, 10018, NULL),
(1000006, '2024-01-25 10:15:00', 'successful', 1000206, 'completed', '7', '2024-06-10 15:00:00', '2024-06-17 12:00:00', 21000.00, 2520.00, 1680.00, 23520.00, 0.00, 103, 10022, NULL),
(1000007, '2024-01-30 12:00:00', 'failed', 1000207, 'ready', '2', '2024-07-01 15:00:00', '2024-07-03 12:00:00', 8000.00, 960.00, 640.00, 8960.00, 0.00, 105, 10015, NULL),
(1000008, '2024-02-02 09:00:00', 'successful', 1000208, 'Completed', '5', '2024-07-15 15:00:00', '2024-07-20 12:00:00', 15000.00, 1800.00, 1200.00, 16800.00, 0.00, 105, 10018, NULL),
(1000009, '2024-02-05 14:45:00', 'in-progress', 1000209, 'pending', '3', '2024-08-05 15:00:00', '2024-08-08 12:00:00', 14000.00, 1680.00, 1120.00, 15680.00, 0.00, 105, 10022, NULL),
(1000010, '2024-02-10 11:30:00', 'failed', 1000210, 'refunded', '4', '2024-09-01 15:00:00', '2024-09-05 12:00:00', 25000.00, 3000.00, 2000.00, 28000.00, 0.00, 105, 10015, 'Customer requested refund due to change in plans'),
(1000011, '2024-02-12 08:00:00', 'successful', 1000201, 'accepted', '6', '2024-10-01 15:00:00', '2024-10-07 12:00:00', 30000.00, 3600.00, 2400.00, 33600.00, 0.00, 102, 10018, NULL),
(1000012, '2024-02-15 15:30:00', 'in-progress', 1000202, 'Checked-In', '2', '2024-11-01 15:00:00', '2024-11-03 12:00:00', 11000.00, 1320.00, 880.00, 12320.00, 0.00, 102, 10022, NULL),
(1000013, '2024-02-18 10:15:00', 'failed', 1000203, 'refunded', '4', '2024-12-01 15:00:00', '2024-12-05 12:00:00', 19000.00, 2280.00, 1520.00, 21280.00, 0.00, 102, 10015, 'Service was not satisfactory as per the traveler'),
(1000014, '2024-02-20 13:45:00', 'successful', 1000204, 'completed', '7', '2024-12-20 15:00:00', '2024-12-27 12:00:00', 22000.00, 2640.00, 1760.00, 24640.00, 0.00, 103, 10018, NULL),
(1000015, '2024-03-01 14:00:00', 'in-progress', 1000205, 'Checked-Out', '3', '2024-01-01 15:00:00', '2024-01-04 12:00:00', 15500.00, 1860.00, 1240.00, 17360.00, 0.00, 103, 10022, NULL),
(1000016, '2024-03-05 10:30:00', 'failed', 1000206, 'pending', '2', '2024-02-01 15:00:00', '2024-02-03 12:00:00', 7000.00, 840.00, 560.00, 7840.00, 0.00, 103, 10015, NULL),
(1000017, '2024-03-10 16:00:00', 'successful', 1000207, 'completed', '5', '2024-03-01 15:00:00', '2024-03-06 12:00:00', 12500.00, 1500.00, 1000.00, 14000.00, 0.00, 105, 10018, NULL),
(1000018, '2024-03-12 14:45:00', 'in-progress', 1000208, 'ready', '6', '2024-04-01 15:00:00', '2024-04-07 12:00:00', 14000.00, 1680.00, 1120.00, 15680.00, 0.00, 105, 10022, NULL),
(1000019, '2024-03-15 11:30:00', 'failed', 1000209, 'pending', '4', '2024-05-01 15:00:00', '2024-05-05 12:00:00', 16500.00, 1980.00, 1320.00, 18480.00, 0.00, 105, 10015, NULL),
(1000020, '2024-03-20 09:15:00', 'successful', 1000210, 'ready', '2', '2024-06-01 15:00:00', '2024-06-03 12:00:00', 11000.00, 1320.00, 880.00, 12320.00, 0.00, 105, 10018, NULL),
(1000021, '2024-04-01 10:00:00', 'in-progress', 1000201, 'Checked-Out', '7', '2024-07-01 15:00:00', '2024-07-08 12:00:00', 22000.00, 2640.00, 1760.00, 24640.00, 0.00, 102, 10022, NULL),
(1000022, '2024-04-05 12:15:00', 'failed', 1000202, 'pending', '3', '2024-08-01 15:00:00', '2024-08-04 12:00:00', 11500.00, 1380.00, 920.00, 12880.00, 0.00, 102, 10015, NULL),
(1000023, '2024-04-10 14:45:00', 'successful', 1000203, 'accepted', '5', '2024-09-01 15:00:00', '2024-09-06 12:00:00', 19000.00, 2280.00, 1520.00, 21280.00, 0.00, 102, 10018, NULL),
(1000024, '2024-04-15 13:00:00', 'in-progress', 1000204, 'Checked-Out', '4', '2024-10-01 15:00:00', '2024-10-05 12:00:00', 16000.00, 1920.00, 1280.00, 17920.00, 0.00, 103, 10022, NULL),
(1000025, '2024-04-20 16:30:00', 'failed', 1000205, 'pending', '2', '2024-11-01 15:00:00', '2024-11-03 12:00:00', 8000.00, 960.00, 640.00, 8960.00, 0.00, 103, 10015, NULL),
(1000026, '2024-05-01 10:30:00', 'successful', 1000206, 'completed', '6', '2024-12-01 15:00:00', '2024-12-07 12:00:00', 18000.00, 2160.00, 1440.00, 20160.00, 0.00, 103, 10018, NULL),
(1000027, '2024-05-05 12:00:00', 'in-progress', 1000207, 'pending', '3', '2024-01-01 15:00:00', '2024-01-04 12:00:00', 10500.00, 1260.00, 840.00, 11760.00, 0.00, 105, 10022, NULL),
(1000028, '2024-05-10 09:30:00', 'failed', 1000208, 'pending', '2', '2024-02-01 15:00:00', '2024-02-03 12:00:00', 6500.00, 780.00, 520.00, 7280.00, 0.00, 105, 10015, NULL),
(1000029, '2024-05-15 14:15:00', 'successful', 1000209, 'ready', '5', '2024-03-01 15:00:00', '2024-03-06 12:00:00', 17000.00, 2040.00, 1360.00, 19040.00, 0.00, 105, 10018, NULL),
(1000030, '2024-05-20 11:00:00', 'in-progress', 1000210, 'Checked-In', '4', '2024-04-01 15:00:00', '2024-04-05 12:00:00', 14000.00, 1680.00, 1120.00, 15680.00, 0.00, 105, 10022, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `inclusions`
--

CREATE TABLE `inclusions` (
  `InclusionID` int(11) NOT NULL,
  `InclusionName` varchar(30) NOT NULL,
  `InclusionDescription` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inclusions`
--

INSERT INTO `inclusions` (`InclusionID`, `InclusionName`, `InclusionDescription`) VALUES
(1, 'Free Breakfast', 'Includes free breakfast for all guests'),
(2, 'Wi-Fi', 'Complimentary high-speed Wi-Fi'),
(3, 'Airport Shuttle', 'Free airport shuttle service'),
(4, 'Pool Access', 'Access to hotel swimming pool'),
(5, 'Gym Access', 'Complimentary access to fitness center'),
(6, 'Parking', 'Free on-site parking available'),
(7, 'Daily Housekeeping', 'Daily cleaning and fresh linens'),
(8, 'In-Room Coffee', 'Complimentary coffee and tea in room'),
(9, 'TV Channels', 'Free access to premium TV channels'),
(10, 'Welcome Drink', 'Complimentary welcome drink upon arrival'),
(11, 'Room Upgrade', 'Complimentary room upgrade upon availability'),
(12, 'Late Checkout', 'Extended checkout time upon request'),
(13, 'Early Check-In', 'Early check-in upon availability'),
(14, 'Mini-Bar', 'Complimentary mini-bar items'),
(15, 'Bathrobe and Slippers', 'Complimentary bathrobe and slippers in room'),
(16, 'Concierge Service', 'Personalized concierge assistance'),
(17, 'Luggage Storage', 'Free luggage storage service'),
(18, 'Beach Access', 'Direct access to the beach'),
(19, 'Spa Credits', 'Credits for resort spa services'),
(20, 'Golf Access', 'Free or discounted access to golf course'),
(21, 'Water Sports', 'Complimentary use of water sports equipment'),
(22, 'Children’s Activities', 'Access to kids’ club and activities'),
(23, 'Dinner Voucher', 'Voucher for a free dinner at on-site restaurant'),
(24, 'Fitness Classes', 'Free participation in fitness classes'),
(25, 'Butler Service', 'Personalized butler service available'),
(26, 'Private Beach Area', 'Exclusive access to private beach area'),
(27, 'Helicopter Transfer', 'Complimentary helicopter transfer service'),
(28, 'Wine Tasting', 'Complimentary wine tasting session'),
(29, 'Personalized Welcome', 'Customized welcome amenities in room'),
(30, 'Gourmet Breakfast', 'Exclusive gourmet breakfast options');

-- --------------------------------------------------------

--
-- Table structure for table `merchant`
--

CREATE TABLE `merchant` (
  `MerchantID` int(3) NOT NULL,
  `BusinessName` varchar(50) NOT NULL,
  `Email` varchar(50) NOT NULL,
  `Contact` varchar(13) NOT NULL,
  `Address` varchar(100) NOT NULL,
  `isApproved` tinyint(4) NOT NULL,
  `BusinessType` varchar(20) NOT NULL,
  `TravelerID` int(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `merchant`
--

INSERT INTO `merchant` (`MerchantID`, `BusinessName`, `Email`, `Contact`, `Address`, `isApproved`, `BusinessType`, `TravelerID`) VALUES
(102, 'Sunset Travel Agency', 'info@sunsettravel.com', '+1234567890', '789 Pine Road, Gotham', 1, 'Travel Agency', 10015),
(103, 'Ocean Breeze Tours', 'contact@oceanbreezetours.com', '+2233445566', '101 Maple Lane, Star City', 1, 'Tour Operator', 10018),
(104, 'Lakeside Adventures', 'support@lakesideadventures.com', '+3344556677', '606 Aspen Street, Lakeside', 0, 'Adventure Travel', 10021),
(105, 'Pleasantville Escapes', 'hello@pleasantvilleescapes.com', '+4455667788', '707 Fir Road, Pleasantville', 1, 'Vacation Packages', 10022);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `header` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `header`, `description`, `created_at`) VALUES
(1, 'Welcome to iWanderPH!', 'Thank you for joining iWanderPH. Start exploring the best travel destinations now!', '2024-09-01 09:00:00'),
(2, 'New Destination Added!', 'Check out the new exclusive destinations available on iWanderPH!', '2024-09-02 10:30:00'),
(3, 'Booking Confirmation', 'Your booking at Boracay Beach Resort has been confirmed.', '2024-09-02 11:00:00'),
(4, 'Limited-Time Offer!', 'Enjoy 20% off on all bookings made this weekend.', '2024-09-01 14:00:00'),
(5, 'Travel Safely', 'Remember to follow health protocols during your travels.', '2024-09-01 16:00:00'),
(6, 'Exclusive Merchant Discounts', 'Merchants now offer special discounts for loyal travelers.', '2024-09-02 09:30:00'),
(7, 'New Feature: Trip Planner', 'Plan your trips with ease using our new Trip Planner feature.', '2024-09-02 08:00:00'),
(8, 'Event Reminder', 'Don\'t miss the travel expo this weekend!', '2024-09-01 18:00:00'),
(9, 'Review Your Stay', 'Please take a moment to review your recent stay at Baguio Hills Resort.', '2024-09-01 20:00:00'),
(10, 'Account Security Alert', 'We\'ve noticed a new login to your account. If this wasn\'t you, please secure your account.', '2024-09-01 22:00:00'),
(11, 'KIM BAHO', 'BISONGGG', '2024-09-07 22:55:35');

-- --------------------------------------------------------

--
-- Table structure for table `review`
--

CREATE TABLE `review` (
  `ReviewID` int(10) NOT NULL,
  `TravelerID` int(5) NOT NULL,
  `BookingID` int(10) NOT NULL,
  `ReviewMessage` text NOT NULL,
  `ReviewRating` int(1) NOT NULL CHECK (`ReviewRating` between 1 and 5),
  `ReviewCreate` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `review`
--

INSERT INTO `review` (`ReviewID`, `TravelerID`, `BookingID`, `ReviewMessage`, `ReviewRating`, `ReviewCreate`) VALUES
(2000010000, 10018, 1000002, 'The stay was pleasant, but the checkout process could be improved.', 4, '2024-03-16 10:00:00'),
(2000010001, 10015, 1000005, 'Great experience overall, but the room could use some updates.', 3, '2024-05-25 09:30:00'),
(2000010002, 10018, 1000008, 'Wonderful stay with excellent service, highly recommend!', 5, '2024-07-21 08:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `RoomID` int(8) NOT NULL,
  `RoomName` varchar(30) NOT NULL,
  `RoomQuantity` int(2) NOT NULL,
  `RoomRate` decimal(10,2) NOT NULL,
  `GuestPerRoom` int(2) NOT NULL,
  `MerchantID` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`RoomID`, `RoomName`, `RoomQuantity`, `RoomRate`, `GuestPerRoom`, `MerchantID`) VALUES
(1000201, 'Deluxe Suite', 5, 5000.00, 2, 102),
(1000202, 'Standard Room', 10, 2500.00, 2, 102),
(1000203, 'Single Room', 8, 2000.00, 1, 102),
(1000204, 'Luxury Cabin', 4, 7000.00, 2, 103),
(1000205, 'Family Suite', 6, 6000.00, 4, 103),
(1000206, 'Economy Room', 12, 1800.00, 2, 103),
(1000207, 'Ocean View Suite', 3, 8000.00, 2, 105),
(1000208, 'Garden Room', 5, 3000.00, 2, 105),
(1000209, 'Cozy Cabin', 7, 2200.00, 2, 105),
(1000210, 'Honeymoon Suite', 2, 12000.00, 2, 105),
(1000214, 'Bed Spacer', 1, 300.00, 1, 102);

-- --------------------------------------------------------

--
-- Table structure for table `room_details`
--

CREATE TABLE `room_details` (
  `RoomID` int(8) NOT NULL,
  `RoomDetails` text NOT NULL,
  `RoomView` varchar(50) NOT NULL,
  `GuestPerRoom` int(2) NOT NULL,
  `RoomQuantity` int(2) NOT NULL,
  `Opt_Inclusions` text NOT NULL,
  `BedType` varchar(50) NOT NULL,
  `Services` text NOT NULL,
  `Gallery` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_details`
--

INSERT INTO `room_details` (`RoomID`, `RoomDetails`, `RoomView`, `GuestPerRoom`, `RoomQuantity`, `Opt_Inclusions`, `BedType`, `Services`, `Gallery`) VALUES
(1000201, 'The Deluxe Suite is a luxurious escape with modern amenities including air conditioning, soundproofing, high-speed Wi-Fi, and a private entrance. It features elegant decor and includes fresh towels, linens, and a well-appointed bath/shower.', 'Garden', 2, 5, 'Parking, Breakfast, Shuttle services', 'King-size bed', 'Daily housekeeping, Shared lounge/TV area, Luggage storage, Wake-up service', 'images/deluxe_suite1.jpg,images/deluxe_suite2.jpg'),
(1000202, 'The Standard Room offers a comfortable retreat with soundproofing, air conditioning, and high-speed Wi-Fi. It is designed with a modern touch and includes fresh towels, linens, and a well-maintained bath/shower.', 'City', 2, 10, 'Breakfast, Activities, Massage', 'Two single beds', 'Daily housekeeping, Luggage storage, Wake-up service', 'images/standard_room1.jpg,images/standard_room2.jpg'),
(1000203, 'The Single Room is an elegant choice with a private balcony, air conditioning, and soundproofing. It includes luxurious towels, linens, and a pristine bath/shower. Enjoy a quiet, private space to unwind.', 'City', 1, 8, 'Parking, Shuttle services, Massage', 'King-size bed', 'Daily housekeeping, Shared lounge/TV area', 'images/single_room1.jpg,images/single_room2.jpg'),
(1000204, 'The Luxury Cabin combines cozy comfort with modern amenities, including soundproofing, air conditioning, and Wi-Fi. Ideal for those seeking a peaceful retreat, it includes fresh towels, linens, and a stylish bath/shower.', 'Mountain', 2, 4, 'Activities, Breakfast, Shuttle services', 'Bunk bed', 'Daily housekeeping, Shared lounge/TV area', 'images/luxury_cabin1.jpg,images/luxury_cabin2.jpg'),
(1000205, 'The Family Suite is a spacious option featuring multiple beds, soundproofing, and air conditioning. Perfect for families, it includes fresh towels, linens, and a comfortable bath/shower.', 'Garden', 4, 6, 'Parking, Breakfast, Activities', 'Two double beds', 'Daily housekeeping, Shared lounge/TV area, Luggage storage', 'images/family_suite1.jpg,images/family_suite2.jpg'),
(1000206, 'The Economy Room provides essential amenities with soundproofing and air conditioning. It is a practical choice for budget travelers, including fresh towels, linens, and a simple bath/shower.', 'Mountain', 2, 12, 'Activities, Massage, Shuttle services', 'Two single beds', 'Daily housekeeping, Wake-up service', 'images/economy_room1.jpg,images/economy_room2.jpg'),
(1000207, 'The Ocean View Suite offers a luxurious experience with stunning ocean views, soundproofing, and modern amenities. It includes fresh towels, linens, and a well-appointed bath/shower for a memorable stay.', 'Beach Front', 2, 3, 'Parking, Breakfast, Activities', 'King-size bed', 'Daily housekeeping, Luggage storage', 'images/ocean_view_suite1.jpg,images/ocean_view_suite2.jpg'),
(1000208, 'The Garden Room features basic amenities with soundproofing and air conditioning, set in a tranquil garden view. It includes fresh towels, linens, and a comfortable bath/shower.', 'Garden', 2, 5, 'Parking, Breakfast, Shuttle services', 'Two single beds', 'Daily housekeeping, Shared lounge/TV area', 'images/garden_room1.jpg,images/garden_room2.jpg'),
(1000209, 'The Cozy Cabin offers modern amenities in a serene mountain setting, including air conditioning, soundproofing, and Wi-Fi. It includes fresh towels, linens, and a relaxing bath/shower.', 'Mountain', 2, 7, 'Parking, Breakfast, Activities', 'Bunk bed', 'Daily housekeeping, Luggage storage', 'images/cozy_cabin1.jpg,images/cozy_cabin2.jpg'),
(1000210, 'The Honeymoon Suite is a luxurious retreat with soundproofing, air conditioning, and stunning beach views. It includes elegant decor, fresh towels, linens, and a luxurious bath/shower for a romantic escape.', 'Beach Front', 2, 2, 'Parking, Breakfast, Activities', 'King-size bed', 'Daily housekeeping, Shared lounge/TV area, Luggage storage', 'images/honeymoon_suite1.jpg,images/honeymoon_suite2.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `room_gallery`
--

CREATE TABLE `room_gallery` (
  `RoomImageID` int(2) NOT NULL,
  `ImageFile` blob NOT NULL,
  `RoomID` int(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `room_inclusions`
--

CREATE TABLE `room_inclusions` (
  `RoomInclusionID` int(3) NOT NULL,
  `InclusionID` int(3) NOT NULL,
  `RoomID` int(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_inclusions`
--

INSERT INTO `room_inclusions` (`RoomInclusionID`, `InclusionID`, `RoomID`) VALUES
(1, 1, 1000201),
(2, 2, 1000201),
(3, 4, 1000201),
(4, 8, 1000201),
(5, 9, 1000201),
(6, 1, 1000202),
(7, 2, 1000202),
(8, 4, 1000202),
(9, 8, 1000202),
(10, 9, 1000202),
(11, 1, 1000203),
(12, 2, 1000203),
(13, 4, 1000203),
(14, 8, 1000203),
(15, 9, 1000203),
(16, 1, 1000204),
(17, 2, 1000204),
(18, 6, 1000204),
(19, 8, 1000204),
(20, 10, 1000204),
(21, 1, 1000205),
(22, 2, 1000205),
(23, 6, 1000205),
(24, 8, 1000205),
(25, 10, 1000205),
(26, 1, 1000206),
(27, 2, 1000206),
(28, 6, 1000206),
(29, 8, 1000206),
(30, 10, 1000206),
(31, 1, 1000207),
(32, 2, 1000207),
(33, 7, 1000207),
(34, 8, 1000207),
(35, 10, 1000207),
(36, 1, 1000208),
(37, 2, 1000208),
(38, 7, 1000208),
(39, 8, 1000208),
(40, 10, 1000208),
(41, 1, 1000209),
(42, 2, 1000209),
(43, 7, 1000209),
(44, 8, 1000209),
(45, 10, 1000209),
(46, 1, 1000210),
(47, 2, 1000210),
(48, 7, 1000210),
(49, 8, 1000210),
(50, 10, 1000210);

-- --------------------------------------------------------

--
-- Table structure for table `room_view`
--

CREATE TABLE `room_view` (
  `RoomViewID` int(3) NOT NULL,
  `ViewID` int(3) NOT NULL,
  `RoomID` int(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_view`
--

INSERT INTO `room_view` (`RoomViewID`, `ViewID`, `RoomID`) VALUES
(1, 1, 1000201),
(2, 2, 1000201),
(3, 4, 1000201),
(4, 5, 1000201),
(5, 9, 1000201),
(6, 2, 1000202),
(7, 4, 1000202),
(8, 6, 1000202),
(9, 8, 1000202),
(10, 10, 1000202),
(11, 1, 1000203),
(12, 3, 1000203),
(13, 5, 1000203),
(14, 7, 1000203),
(15, 9, 1000203),
(16, 1, 1000204),
(17, 2, 1000204),
(18, 6, 1000204),
(19, 8, 1000204),
(20, 10, 1000204),
(21, 3, 1000205),
(22, 4, 1000205),
(23, 7, 1000205),
(24, 9, 1000205),
(25, 11, 1000205),
(26, 2, 1000206),
(27, 5, 1000206),
(28, 6, 1000206),
(29, 8, 1000206),
(30, 12, 1000206),
(31, 1, 1000207),
(32, 2, 1000207),
(33, 7, 1000207),
(34, 8, 1000207),
(35, 9, 1000207),
(36, 4, 1000208),
(37, 5, 1000208),
(38, 6, 1000208),
(39, 8, 1000208),
(40, 10, 1000208),
(41, 7, 1000209),
(42, 8, 1000209),
(43, 9, 1000209),
(44, 11, 1000209),
(45, 12, 1000209),
(46, 1, 1000210),
(47, 5, 1000210),
(48, 7, 1000210),
(49, 9, 1000210),
(50, 10, 1000210);

-- --------------------------------------------------------

--
-- Table structure for table `transportations`
--

CREATE TABLE `transportations` (
  `TransportationID` int(8) NOT NULL,
  `VehicleName` varchar(30) NOT NULL,
  `Model` varchar(30) NOT NULL,
  `Brand` varchar(30) NOT NULL,
  `Capacity` varchar(2) NOT NULL,
  `RentalPrice` decimal(10,2) NOT NULL,
  `MerchantID` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transportations`
--

INSERT INTO `transportations` (`TransportationID`, `VehicleName`, `Model`, `Brand`, `Capacity`, `RentalPrice`, `MerchantID`) VALUES
(1000101, 'Luxury Sedan', 'S-Class', 'Mercedes', '4', 8000.00, 102),
(1000102, 'SUV', 'X5', 'BMW', '5', 12000.00, 102),
(1000103, 'Minivan', 'Odyssey', 'Honda', '7', 15000.00, 102),
(1000104, 'Standard Taxi', 'Camry', 'Toyota', '4', 3000.00, 102),
(1000105, 'Convertible', 'Mustang', 'Ford', '2', 5000.00, 103),
(1000106, 'Luxury Coach', 'Neoplan', 'Mercedes', '50', 35000.00, 103),
(1000107, 'Standard Bus', 'Sprinter', 'Mercedes', '20', 20000.00, 103),
(1000108, 'Executive Van', 'Elgrand', 'Nissan', '8', 18000.00, 105);

-- --------------------------------------------------------

--
-- Table structure for table `transportation_gallery`
--

CREATE TABLE `transportation_gallery` (
  `TranspotationImageID` int(2) NOT NULL,
  `ImageFile` blob NOT NULL,
  `TransportationID` int(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `traveler`
--

CREATE TABLE `traveler` (
  `TravelerID` int(5) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `Email` varchar(50) NOT NULL,
  `Mobile` varchar(13) NOT NULL,
  `Address` varchar(100) NOT NULL,
  `ProfilePic` blob DEFAULT NULL,
  `Username` varchar(30) NOT NULL,
  `Password` varchar(30) NOT NULL,
  `isMerchant` tinyint(4) NOT NULL,
  `isDeactivated` tinyint(4) NOT NULL,
  `isSuspended` tinyint(4) NOT NULL,
  `isBanned` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `traveler`
--

INSERT INTO `traveler` (`TravelerID`, `FirstName`, `LastName`, `Email`, `Mobile`, `Address`, `ProfilePic`, `Username`, `Password`, `isMerchant`, `isDeactivated`, `isSuspended`, `isBanned`) VALUES
(10001, 'John', 'Doe', 'johndoe@example.com', '09123456789', '123 Traveler St, City', NULL, 'GlobeTrotterJohn', 'pass1234', 0, 0, 0, 0),
(10002, 'Jane', 'Smith', 'janesmith@example.com', '09198765432', '456 Wanderer Ave, Town', NULL, 'WanderlustJane', 'password123', 0, 0, 0, 0),
(10003, 'Michael', 'Johnson', 'mjohnson@example.com', '09234567891', '789 Nomad Lane, Village', NULL, 'NomadicMike', 'travelpass', 0, 0, 0, 0),
(10004, 'Emily', 'Brown', 'ebrown@example.com', '09345678912', '101 Explorer Blvd, City', NULL, 'ExplorerEmily', 'securepass', 1, 0, 0, 0),
(10005, 'David', 'Davis', 'ddavis@example.com', '09456789123', '202 Adventure Rd, Suburb', NULL, 'AdventureDave', 'adventure123', 0, 0, 0, 0),
(10006, 'Sarah', 'Wilson', 'swilson@example.com', '09567891234', '303 GlobeTrotter St, City', NULL, 'GlobeTrotterSarah', 'globetrot123', 0, 1, 0, 0),
(10007, 'Chris', 'Taylor', 'ctaylor@example.com', '09678912345', '404 Trekking Way, Village', NULL, 'TrekkingChris', 'trekkingpass', 0, 0, 1, 0),
(10008, 'Anna', 'Lee', 'alee@example.com', '09789123456', '505 Backpacker Rd, Town', NULL, 'BackpackerAnna', 'backpack123', 1, 0, 0, 0),
(10009, 'James', 'White', 'jwhite@example.com', '09891234567', '606 Traveler Lane, City', NULL, 'TravelingJames', 'traveling123', 0, 0, 0, 1),
(10010, 'Laura', 'Martin', 'lmartin@example.com', '09912345678', '707 Explorer St, Village', NULL, 'ExplorerLaura', 'explore456', 1, 0, 0, 0),
(10011, 'Robert', 'Clark', 'rclark@example.com', '09123450011', '808 Adventure Blvd, City', NULL, 'AdventureRobert', 'adventure789', 0, 0, 0, 0),
(10012, 'Linda', 'Harris', 'lharris@example.com', '09123450012', '909 GlobeTrotter Ave, City', NULL, 'WanderLinda', 'wander789', 0, 0, 0, 0),
(10013, 'Daniel', 'Lewis', 'dlewis@example.com', '09123450013', '1010 Nomad St, Suburb', NULL, 'NomadDaniel', 'nomad123', 0, 0, 0, 0),
(10014, 'Jessica', 'Walker', 'jwalker@example.com', '09123450014', '1111 Explorer Ln, Town', NULL, 'ExplorerJess', 'explore123', 0, 0, 0, 0),
(10015, 'Mark', 'Allen', 'mallen@example.com', '09123450015', '1212 Wanderer Blvd, City', NULL, 'WandererMark', 'markwander', 1, 0, 0, 0),
(10016, 'Patricia', 'Young', 'pyoung@example.com', '09123450016', '1313 Traveler Rd, Village', NULL, 'TravelerPat', 'pat123', 0, 1, 0, 0),
(10017, 'Joshua', 'King', 'jking@example.com', '09123450017', '1414 Backpacker Way, Suburb', NULL, 'BackpackerJosh', 'backpack789', 0, 0, 0, 0),
(10018, 'Olivia', 'Scott', 'oscott@example.com', '09123450018', '1515 Trekker Blvd, Town', NULL, 'TrekkerOlivia', 'trek789', 1, 0, 0, 0),
(10019, 'Matthew', 'Green', 'mgreen@example.com', '09123450019', '1616 GlobeTrotter St, City', NULL, 'GlobeTrotMatt', 'globetrot789', 0, 0, 1, 0),
(10020, 'Sophia', 'Adams', 'sadams@example.com', '09123450020', '1717 Wanderlust Rd, Town', NULL, 'WanderSophia', 'wander456', 1, 0, 0, 0),
(10021, 'Ryan', 'Perez', 'rperez@example.com', '09123450021', '1818 Nomad Ln, Village', NULL, 'NomadRyan', 'nomad456', 0, 0, 0, 0),
(10022, 'Grace', 'Evans', 'gevans@example.com', '09123450022', '1919 Adventure Ave, Suburb', NULL, 'AdventureGrace', 'grace789', 1, 0, 0, 0),
(10023, 'Samuel', 'Baker', 'sbaker@example.com', '09123450023', '2020 Explorer St, City', NULL, 'ExplorerSam', 'explore456', 0, 0, 0, 0),
(10024, 'Mia', 'Garcia', 'mgarcia@example.com', '09123450024', '2121 Trekker Rd, Village', NULL, 'TrekkerMia', 'trek123', 0, 1, 0, 0),
(10025, 'Ethan', 'Martinez', 'emartinez@example.com', '09123450025', '2222 Traveler Ln, Town', NULL, 'TravelerEthan', 'ethan123', 0, 0, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `views`
--

CREATE TABLE `views` (
  `ViewID` int(3) NOT NULL,
  `ViewName` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `views`
--

INSERT INTO `views` (`ViewID`, `ViewName`) VALUES
(1, 'Ocean View'),
(2, 'Mountain View'),
(3, 'City Skyline View'),
(4, 'Garden View'),
(5, 'Pool View'),
(6, 'River View'),
(7, 'Lake View'),
(8, 'Forest View'),
(9, 'Beach View'),
(10, 'Sunset View'),
(11, 'Golf Course View'),
(12, 'Valley View'),
(13, 'Historical Landmark View'),
(14, 'Skyline View'),
(15, 'Harbor View');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`AdminID`),
  ADD UNIQUE KEY `Password` (`Password`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD KEY `idx_booking_id` (`BookingID`);

--
-- Indexes for table `inclusions`
--
ALTER TABLE `inclusions`
  ADD PRIMARY KEY (`InclusionID`);

--
-- Indexes for table `merchant`
--
ALTER TABLE `merchant`
  ADD PRIMARY KEY (`MerchantID`),
  ADD KEY `TravelerID` (`TravelerID`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`ReviewID`,`TravelerID`,`BookingID`),
  ADD KEY `TravelerID` (`TravelerID`),
  ADD KEY `BookingID` (`BookingID`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`RoomID`),
  ADD KEY `MerchantID` (`MerchantID`);

--
-- Indexes for table `room_details`
--
ALTER TABLE `room_details`
  ADD PRIMARY KEY (`RoomID`);

--
-- Indexes for table `room_gallery`
--
ALTER TABLE `room_gallery`
  ADD PRIMARY KEY (`RoomImageID`),
  ADD KEY `RoomID` (`RoomID`);

--
-- Indexes for table `room_inclusions`
--
ALTER TABLE `room_inclusions`
  ADD PRIMARY KEY (`RoomInclusionID`),
  ADD KEY `RoomID` (`RoomID`);

--
-- Indexes for table `room_view`
--
ALTER TABLE `room_view`
  ADD PRIMARY KEY (`RoomViewID`),
  ADD KEY `RoomID` (`RoomID`);

--
-- Indexes for table `transportations`
--
ALTER TABLE `transportations`
  ADD PRIMARY KEY (`TransportationID`),
  ADD KEY `MerchantID` (`MerchantID`);

--
-- Indexes for table `transportation_gallery`
--
ALTER TABLE `transportation_gallery`
  ADD PRIMARY KEY (`TranspotationImageID`),
  ADD KEY `TransportationID` (`TransportationID`);

--
-- Indexes for table `traveler`
--
ALTER TABLE `traveler`
  ADD PRIMARY KEY (`TravelerID`),
  ADD KEY `idx_traveler_id` (`TravelerID`);

--
-- Indexes for table `views`
--
ALTER TABLE `views`
  ADD PRIMARY KEY (`ViewID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `AdminID` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `inclusions`
--
ALTER TABLE `inclusions`
  MODIFY `InclusionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `merchant`
--
ALTER TABLE `merchant`
  MODIFY `MerchantID` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `review`
--
ALTER TABLE `review`
  MODIFY `ReviewID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2000010003;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `RoomID` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1000216;

--
-- AUTO_INCREMENT for table `room_gallery`
--
ALTER TABLE `room_gallery`
  MODIFY `RoomImageID` int(2) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `room_inclusions`
--
ALTER TABLE `room_inclusions`
  MODIFY `RoomInclusionID` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `room_view`
--
ALTER TABLE `room_view`
  MODIFY `RoomViewID` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `transportations`
--
ALTER TABLE `transportations`
  MODIFY `TransportationID` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1000109;

--
-- AUTO_INCREMENT for table `transportation_gallery`
--
ALTER TABLE `transportation_gallery`
  MODIFY `TranspotationImageID` int(2) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `views`
--
ALTER TABLE `views`
  MODIFY `ViewID` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `merchant`
--
ALTER TABLE `merchant`
  ADD CONSTRAINT `merchant_ibfk_1` FOREIGN KEY (`TravelerID`) REFERENCES `traveler` (`TravelerID`);

--
-- Constraints for table `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`TravelerID`) REFERENCES `traveler` (`TravelerID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `review_ibfk_2` FOREIGN KEY (`BookingID`) REFERENCES `bookings` (`BookingID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`MerchantID`) REFERENCES `merchant` (`MerchantID`);

--
-- Constraints for table `room_details`
--
ALTER TABLE `room_details`
  ADD CONSTRAINT `room_details_ibfk_1` FOREIGN KEY (`RoomID`) REFERENCES `rooms` (`RoomID`);

--
-- Constraints for table `room_gallery`
--
ALTER TABLE `room_gallery`
  ADD CONSTRAINT `room_gallery_ibfk_1` FOREIGN KEY (`RoomID`) REFERENCES `rooms` (`RoomID`);

--
-- Constraints for table `room_inclusions`
--
ALTER TABLE `room_inclusions`
  ADD CONSTRAINT `room_inclusions_ibfk_1` FOREIGN KEY (`RoomID`) REFERENCES `rooms` (`RoomID`);

--
-- Constraints for table `room_view`
--
ALTER TABLE `room_view`
  ADD CONSTRAINT `room_view_ibfk_1` FOREIGN KEY (`RoomID`) REFERENCES `rooms` (`RoomID`);

--
-- Constraints for table `transportations`
--
ALTER TABLE `transportations`
  ADD CONSTRAINT `transportations_ibfk_1` FOREIGN KEY (`MerchantID`) REFERENCES `merchant` (`MerchantID`);

--
-- Constraints for table `transportation_gallery`
--
ALTER TABLE `transportation_gallery`
  ADD CONSTRAINT `transportation_gallery_ibfk_1` FOREIGN KEY (`TransportationID`) REFERENCES `transportations` (`TransportationID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
