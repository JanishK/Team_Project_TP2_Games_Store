-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 04, 2025 at 05:01 AM
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
-- Database: `game_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `game_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(3) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE `games` (
  `gid` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `platform` enum('PC','Playstation','Xbox','Nintendo Switch') NOT NULL,
  `price` decimal(5,2) NOT NULL,
  `image` varchar(200) NOT NULL,
  `age_restriction` enum('8','13','16','18+') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `games`
--

INSERT INTO `games` (`gid`, `name`, `description`, `platform`, `price`, `image`, `age_restriction`) VALUES
(3, 'The Legend of Zelda: Breath of the Wild (Nintendo Switch)', 'Step into a vast open world of adventure in The Legend of Zelda: Breath of the Wild, one of Nintendo’s most acclaimed masterpieces. Play as Link, awakened from a long slumber to find the kingdom of Hyrule in ruins and overcome by the dark power of Calamity Ganon.\r\n\r\nExplore breathtaking landscapes, solve ancient puzzles, battle formidable enemies, and uncover the secrets of a world that changes with every step. Featuring dynamic weather, physics-driven gameplay, challenging shrines, and total freedom to choose your own path, this game redefines what open-world exploration can be.\r\n\r\nWhether you’re gliding across mountains, cooking up survival recipes, or discovering hidden treasures, Breath of the Wild offers a deeply immersive experience that appeals to both longtime fans and newcomers.\r\n\r\nKey Features:\r\n\r\nMassive open world filled with secrets and exploration\r\n\r\nOver 100 shrines and countless puzzles\r\n\r\nInnovative weapon, survival, and physics systems\r\n\r\nBeautiful visuals and atmospheric soundtrack\r\n\r\nFreedom to approach challenges any way you choose', 'Nintendo Switch', 44.99, 'The_Legend_of_Zelda_Breath_of_the_Wild.jpg', '13'),
(4, 'Mario Kart 8 Deluxe (Nintendo Switch)', 'Get ready to race, drift, and battle your way to victory with Mario Kart 8 Deluxe, the ultimate multiplayer racing experience on the Nintendo Switch. Packed with fan-favorite characters, creative tracks, and thrilling gameplay modes, this definitive edition delivers nonstop fun for all ages.\r\n\r\nJump into high-speed action across 48 tracks, including classics and DLC favorites, all running in smooth HD. Whether you’re playing locally with friends, racing online, or taking on the Grand Prix solo, every match feels fast, chaotic, and endlessly replayable.\r\n\r\nWith improved battle modes, new characters like Inkling Boy & Girl, and convenient features such as Smart Steering for younger or newer players, Mario Kart 8 Deluxe is the must-have party game for the Switch.\r\n\r\nKey Features:\r\n\r\nIncludes all tracks and characters from the original + DLC\r\n\r\nLocal and online multiplayer for up to 8 players\r\n\r\nRevamped Battle Mode with new arenas\r\n\r\nSmooth, vibrant HD graphics and tight controls\r\n\r\nPerfect for parties, family gaming, or competitive play', 'Nintendo Switch', 39.99, 'Mario Kart 8 Deluxe (Nintendo Switch).jpg', '8'),
(5, 'ARMS (Nintendo Switch)', 'Step into the arena and throw punches that stretch across the screen in ARMS, Nintendo’s unique motion-controlled fighting game for the Nintendo Switch. Choose from a colorful roster of fighters—each with their own extendable, spring-loaded arms—and battle your way through fast, strategic, and highly energetic matchups.\r\n\r\nUse motion controls with the Joy-Cons or traditional button input to dodge, block, curve punches, and unleash powerful combos. Customize your fighter with a wide variety of arm types, each offering different abilities, weight classes, and elemental effects to fit your playstyle.\r\n\r\nWhether playing solo, competing locally with friends, or challenging opponents online, ARMS delivers a fun, stylish, and competitive experience that’s easy to learn and hard to put down.\r\n\r\nKey Features:\r\n\r\nUnique motion-controlled or button-based fighting gameplay\r\n\r\nDiverse roster of fighters with customizable arm loadouts\r\n\r\nLocal, online, and split-screen multiplayer modes\r\n\r\nBright, energetic visuals and fast-paced action\r\n\r\nAccessible for beginners, rewarding for skilled players', 'Nintendo Switch', 44.99, 'Arms_(video_game).jpg', '13'),
(6, 'Grand Theft Auto V (PS5)', 'Experience the definitive version of Rockstar’s open-world blockbuster with Grand Theft Auto V for PlayStation 5. Enhanced with stunning 4K resolution, faster loading times, improved textures, ray tracing, and smooth 60 FPS performance, this PS5 edition delivers the most immersive Los Santos experience yet.\r\n\r\nSwitch between the three iconic protagonists—Michael, Franklin, and Trevor—as you take on daring heists, explosive missions, and a massive world filled with endless activities. From city streets to mountain ranges and ocean depths, Los Santos and Blaine County feel more alive than ever.\r\n\r\nJump into GTA Online, featuring years of updates, jobs, vehicles, businesses, and multiplayer modes. Build your criminal empire, race, compete, or simply explore with friends—PS5 players also benefit from performance boosts and improved visuals in online play.\r\n\r\nKey Features:\r\n\r\nEnhanced graphics with 4K, HDR, ray tracing, and 60 FPS modes\r\n\r\nLightning-fast load times and improved gameplay responsiveness\r\n\r\nThe full single-player story with three playable protagonists\r\n\r\nAccess to GTA Online with expanded features and updates\r\n\r\nA massive open world packed with missions, heists, vehicles, and customization', 'Playstation', 15.99, 'gta5.jpg', '18+'),
(7, 'Grand Theft Auto V (Xbox Series X)', 'Step into the ultimate open-world adventure with Grand Theft Auto V for Xbox Series X. Experience Los Santos like never before with stunning 4K visuals, HDR support, faster load times, and smooth 60 FPS gameplay.\r\n\r\nPlay as the three iconic protagonists—Michael, Franklin, and Trevor—and take on daring heists, intense missions, and countless side activities across a massive, living world. Whether navigating city streets, exploring wilderness, or diving underwater, every corner of Los Santos is packed with detail and action.\r\n\r\nJump into GTA Online to enjoy years of updates, including new vehicles, missions, multiplayer modes, and customization options. The Xbox Series X version also offers performance and visual upgrades that enhance both single-player and online experiences.\r\n\r\nKey Features:\r\n\r\nEnhanced graphics with 4K, HDR, and 60 FPS gameplay\r\n\r\nFaster loading times for seamless play\r\n\r\nFull single-player story with three playable protagonists\r\n\r\nAccess to GTA Online with expanded content and updates\r\n\r\nHuge open world filled with missions, heists, vehicles, and endless exploration', 'Xbox', 15.99, 'gta5.jpg', '18+'),
(8, 'Minecraft: Deluxe Collection |Xbox One/Xbox Series X|S ', 'Unleash your creativity with Minecraft: Deluxe Collection on Xbox One and Xbox Series X|S! This ultimate edition brings together everything you need to build, explore, and survive in the iconic block-based world of Minecraft.\r\n\r\nCraft structures, mine resources, and explore endless landscapes—forests, caves, oceans, and mountains—while facing monsters or playing peacefully in Creative Mode. With multiplayer support, you can join friends locally or online to build, adventure, and survive together.\r\n\r\nThe Deluxe Collection includes bonus content, expansions, and skins, giving you even more ways to personalize your world and gameplay experience. Perfect for newcomers, longtime fans, or anyone who loves boundless creativity.\r\n\r\nKey Features:\r\n\r\nBuild, mine, explore, and survive in a limitless sandbox world\r\n\r\nIncludes bonus packs, expansions, and skins for added fun\r\n\r\nPlay solo, locally, or online with friends\r\n\r\nOptimized for Xbox One and Xbox Series X|S\r\n\r\nEndless creativity, adventure, and multiplayer fun', 'Xbox', 24.99, 'Mincraft.jpeg', '8'),
(9, 'Spider-Man 2 Standard PlayStation 5', 'Swing into action with Spider-Man 2 on PlayStation 5! Join Peter Parker and Miles Morales as they face new villains, navigate the streets of New York City, and protect the city in an all-new, action-packed adventure.\r\n\r\nExperience thrilling web-slinging, fast-paced combat, and breathtaking visuals powered by the PS5. With a dynamic open-world, intuitive controls, and immersive storytelling, Spider-Man 2 delivers a superhero experience like no other.\r\n\r\nKey Features:\r\n\r\nPlay as both Peter Parker and Miles Morales\r\n\r\nDynamic open-world exploration of New York City\r\n\r\nIntense combat, acrobatic moves, and web-based traversal\r\n\r\nStunning PS5 graphics with fast loading times\r\n\r\nEngaging story with new villains and epic missions', 'Playstation', 29.99, 'spiderman.jpeg', '16'),
(10, 'God of War Ragnarök [PlayStation 5]', 'Step into the epic conclusion of Kratos and Atreus’ journey in God of War Ragnarök for PlayStation 5. Explore the realms of Norse mythology, battle formidable gods and monsters, and experience an emotionally charged story that pushes the boundaries of action-adventure gaming.\r\n\r\nWith stunning next-gen visuals, fast loading times, and fluid combat mechanics, Ragnarök delivers a cinematic experience full of strategic battles, exploration, and deep narrative choices. Whether wielding the Leviathan Axe, Blades of Chaos, or magical abilities, every fight is visceral and satisfying.\r\n\r\nKey Features:\r\n\r\nEpic story continuing the journey of Kratos and Atreus\r\n\r\nStunning PS5 graphics with enhanced performance\r\n\r\nIntense combat with multiple weapons, abilities, and upgrades\r\n\r\nExploration across the realms of Norse mythology\r\n\r\nDeep narrative, cinematic storytelling, and side quests', 'Playstation', 39.72, 'God_of_War_Ragnarök_cover.jpg', '18+'),
(11, 'The Last Of Us Part II (Remastered) PS5', 'Experience the critically acclaimed, emotionally powerful story of The Last of Us Part II, now fully remastered for PlayStation 5. Join Ellie as she navigates a post-apocalyptic world filled with danger, tough choices, and hauntingly beautiful environments.\r\n\r\nThe PS5 remaster enhances visuals, improves performance, and reduces loading times, delivering a more immersive experience than ever. Engage in tense stealth gameplay, intense combat, and explore a world that is as captivating as it is brutal.\r\n\r\nKey Features:\r\n\r\nFully remastered for PS5 with enhanced graphics and performance\r\n\r\nImmersive storytelling with rich, emotionally driven characters\r\n\r\nIntense stealth and combat mechanics\r\n\r\nExplore a detailed, post-apocalyptic world\r\n\r\nFast loading times and optimized gameplay for next-gen consoles', 'Playstation', 21.49, 'The Last Of Us Part II (Remastered) PS5.avif', '18+'),
(13, 'Gran Turismo 7 (PS5)', 'Experience the pinnacle of racing simulation with Gran Turismo 7 on PlayStation 5. Featuring stunning next-gen graphics, realistic physics, and a massive collection of cars and tracks, GT7 delivers the ultimate driving experience for casual players and racing enthusiasts alike.\r\n\r\nRace across iconic locations around the world, customize and tune your vehicles, and take part in a variety of modes, from high-speed single-player campaigns to competitive online racing. The PS5 edition also benefits from fast loading times, immersive 3D audio, and enhanced haptic feedback for unparalleled realism.\r\n\r\nKey Features:\r\n\r\nRealistic driving physics and stunning PS5 visuals\r\n\r\nExtensive car collection with detailed customization options\r\n\r\nIconic tracks and dynamic weather conditions\r\n\r\nSingle-player campaigns, multiplayer, and online competitions\r\n\r\nEnhanced performance with fast loading times and immersive feedback', 'Playstation', 29.99, 'Gran turismo.jpeg', '8'),
(14, 'Elden Ring Xbox One & Xbox Series X', 'Step into a vast, dark fantasy world with Elden Ring, the critically acclaimed action RPG from FromSoftware. Explore an open-world filled with breathtaking landscapes, dangerous dungeons, and formidable enemies as you uncover the secrets of the Lands Between.\r\n\r\nMaster challenging combat, customize your character, and choose your path in a world that rewards exploration, strategy, and skill. Whether playing solo or online with others, Elden Ring delivers a deeply immersive experience with endless challenges and discovery.\r\n\r\nKey Features:\r\n\r\nOpen-world action RPG with rich lore and exploration\r\n\r\nChallenging combat and deep customization options\r\n\r\nPlay on Xbox One or take advantage of Xbox Series X enhancements\r\n\r\nExplore dungeons, defeat bosses, and uncover hidden secrets\r\n\r\nSolo or online multiplayer modes for cooperative or competitive play', 'Xbox', 21.99, 'elden.jpg', '16');

-- --------------------------------------------------------

--
-- Table structure for table `game_genres`
--

CREATE TABLE `game_genres` (
  `game_id` int(11) UNSIGNED NOT NULL,
  `genre_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `game_genres`
--

INSERT INTO `game_genres` (`game_id`, `genre_id`) VALUES
(3, 2),
(3, 1),
(3, 3),
(4, 8),
(5, 7),
(6, 1),
(6, 2),
(7, 1),
(7, 2),
(8, 10),
(8, 2),
(8, 14),
(9, 1),
(9, 2),
(10, 1),
(10, 3),
(11, 1),
(11, 9),
(11, 2),
(13, 8),
(13, 10),
(14, 3),
(14, 1),
(14, 2);

-- --------------------------------------------------------

--
-- Table structure for table `genres`
--

CREATE TABLE `genres` (
  `genre_id` int(11) UNSIGNED NOT NULL,
  `name` varchar(70) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `genres`
--

INSERT INTO `genres` (`genre_id`, `name`) VALUES
(1, 'Action'),
(2, 'Adventure'),
(7, 'Fighting'),
(9, 'Horror'),
(6, 'MMO'),
(5, 'Puzzle'),
(8, 'Racing'),
(3, 'RPG'),
(4, 'Shooter'),
(10, 'Simulation'),
(11, 'Sports'),
(12, 'Stealth'),
(13, 'Strategy'),
(14, 'Survival');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `rid` int(11) UNSIGNED NOT NULL,
  `game_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL CHECK (`rating` between 0 and 5),
  `comment` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `uid` int(10) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`uid`, `username`, `password`, `email`, `is_admin`) VALUES
(1, 'Abdirahman', '$2y$10$djQcGlK671HiEpEImC7HhO0E5LbqJ8Ut0l0g452OSvTvYlHNXvBRK', 'tahlilabdurahman@gmail.com', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indexes for table `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`gid`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `game_genres`
--
ALTER TABLE `game_genres`
  ADD KEY `game_id` (`game_id`),
  ADD KEY `genre_id` (`genre_id`);

--
-- Indexes for table `genres`
--
ALTER TABLE `genres`
  ADD PRIMARY KEY (`genre_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`rid`),
  ADD KEY `game_id` (`game_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`uid`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
  MODIFY `gid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `genres`
--
ALTER TABLE `genres`
  MODIFY `genre_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `rid` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `uid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`uid`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `games` (`gid`) ON DELETE CASCADE;

--
-- Constraints for table `game_genres`
--
ALTER TABLE `game_genres`
  ADD CONSTRAINT `game_genres_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`gid`) ON DELETE CASCADE,
  ADD CONSTRAINT `game_genres_ibfk_2` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`genre_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`gid`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`uid`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;