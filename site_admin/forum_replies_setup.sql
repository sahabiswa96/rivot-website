-- Forum replies table
CREATE TABLE IF NOT EXISTS forum_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    author VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    status ENUM('active', 'deleted', 'moderated') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES forum_posts(id) ON DELETE CASCADE
);

-- Insert some demo replies for existing posts
INSERT INTO forum_replies (post_id, author, content) VALUES
(1, 'BatteryExpert', 'Great question! I''ve been using my Classic for 18 months and here''s what I''ve learned: Never let the battery go below 20% if you can help it. Lithium-ion batteries last longer when kept between 20-80% charge.'),
(1, 'TechGuru', 'I second that advice! Also, avoid charging immediately after a long ride when the battery is hot. Let it cool down for 15-20 minutes first.'),
(1, 'AlexRider', 'Thanks for the tips everyone! I''ll definitely start following the 20-80% rule. @BatteryExpert do you have any specific app recommendations for monitoring battery health?'),
(1, 'RivotOwner2021', 'The RIVOT mobile app actually has a battery health section that shows detailed statistics. Check under Settings > Battery Health.'),

(2, 'WeekendRider', 'Count me in! I''ve been looking for an excuse to take my Pro out for a longer ride. What''s the weather forecast looking like?'),
(2, 'NandiHillsLocal', 'Perfect timing! The weather should be clear and cool in the morning. Just watch out for the construction near Devanahalli - might need to take the alternate route.'),
(2, 'GroupRideEnthusiast', 'This sounds amazing! Should I bring my portable charger just in case? Also, is there a WhatsApp group for coordination?'),

(3, 'ClassicOwner', 'I was in the exact same situation last year! I went with the Classic and honestly, it''s been perfect for city commuting. The Pro might be overkill unless you''re planning longer trips.'),
(3, 'ProEnthusiast', 'Disagree! The extra range on the Pro is worth it. You never know when you might need that extra juice, especially in Bangalore traffic with all the detours.'),
(3, 'CityCommuter', 'Thanks for the perspectives! @ClassicOwner how''s the acceleration in stop-and-go traffic? That''s my main concern.'),

(4, 'DIYFanatic', 'This is exactly what I was looking for! Do you have photos of the finished installation? Also, where did you source the waterproof box?'),
(4, 'ToolKitCollector', 'Brilliant idea! I''ve been keeping my tools in my backpack but this would be so much more convenient. What''s the weight impact?'),
(4, 'ModMaster', 'I''ll post detailed photos tomorrow! The box adds maybe 200-300 grams, completely negligible. Got it from a local electronics store in SP Road.'),

(5, 'ServiceSurvivor', 'OMG yes! I had the EXACT same experience at the same service center! They quoted me ₹8000 for what should have been a ₹500 fix. Absolutely avoid this place.'),
(5, 'ElectronicCityUser', 'The Electronic City service center is fantastic! I''ve been going there for 2 years and never had an issue. Professional staff and fair pricing.'),
(5, 'WhitefieldRider', 'Adding to the good list: Whitefield service center is also excellent. Quick turnaround and they actually explain what they''re fixing.'),

(6, 'DealSeeker', 'I''m definitely interested in the phone holder and LED strips! Do we need to pay upfront or can we pay when collecting?'),
(6, 'AccessoryLover', 'The rear storage box sounds perfect for my daily commute. @DealHunter is installation really free or is there a catch?'),
(6, 'CustomSeatFan', 'Count me in for the custom seat covers! My current seat gets quite hot in the summer. What color options are available?');

-- Update forum_posts reply counts based on actual replies
UPDATE forum_posts SET replies = (
    SELECT COUNT(*) FROM forum_replies WHERE post_id = forum_posts.id AND status = 'active'
) WHERE id <= 6;