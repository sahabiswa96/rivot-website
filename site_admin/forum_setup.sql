-- Forum management tables
CREATE TABLE IF NOT EXISTS forum_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    icon VARCHAR(50) NOT NULL DEFAULT 'fas fa-comments',
    color VARCHAR(7) NOT NULL DEFAULT '#CE6723',
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS forum_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    excerpt VARCHAR(500) NOT NULL,
    author VARCHAR(100) NOT NULL,
    category_id INT NOT NULL,
    icon VARCHAR(50) NOT NULL DEFAULT 'fas fa-comments',
    replies INT DEFAULT 0,
    views INT DEFAULT 0,
    status ENUM('active', 'locked', 'pinned') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES forum_categories(id) ON DELETE CASCADE
);

-- Insert default forum categories
INSERT INTO forum_categories (name, description, icon, sort_order) VALUES
('General Discussion', 'General discussions about electric scooters, news, and announcements', 'fas fa-comments', 1),
('Model Discussions', 'Discuss specific RIVOT models, features, and comparisons', 'fas fa-motorcycle', 2),
('Tips & Tricks', 'Share maintenance tips, riding techniques, and customization ideas', 'fas fa-lightbulb', 3),
('Ride Stories', 'Share your adventures, group rides, and travel experiences', 'fas fa-road', 4),
('Technical Support', 'Get help with technical issues, troubleshooting, and repairs', 'fas fa-tools', 5),
('Marketplace', 'Buy, sell, or trade RIVOT accessories and related products', 'fas fa-shopping-cart', 6);

-- Insert demo forum posts
INSERT INTO forum_posts (title, content, excerpt, author, category_id, icon, replies, views) VALUES
(
    'Best practices for battery longevity?',
    '<p>I''ve had my Classic for 6 months now and want to make sure I''m taking good care of the battery. What are the best charging practices to maximize battery life?</p>

<p>So far I''ve been:</p>
<ul>
<li>Charging it every night regardless of usage</li>
<li>Using the original charger only</li>
<li>Keeping it in the garage (temperature controlled)</li>
</ul>

<p>Are there any other best practices I should follow? Should I avoid charging to 100% regularly? What about letting it drain completely?</p>

<p>Would love to hear from experienced riders about their battery care routines!</p>',
    'I''ve had my Classic for 6 months now and want to make sure I''m taking good care of the battery. What are the best charging practices...',
    'AlexRider',
    3,
    'fas fa-bolt',
    12,
    245
),
(
    'Weekend ride to Nandi Hills - Join us!',
    '<p>Planning a group ride to Nandi Hills this Saturday. We''ll start from MG Road at 6 AM and take the scenic route through Yelahanka.</p>

<p><strong>Details:</strong></p>
<ul>
<li>Date: Saturday, January 20th</li>
<li>Start Time: 6:00 AM</li>
<li>Meeting Point: MG Road Metro Station</li>
<li>Distance: ~60km each way</li>
<li>Expected return: 2:00 PM</li>
</ul>

<p>We''ll have breakfast at the top and enjoy the sunrise. Make sure your scooters are fully charged as we''ll be covering about 120km total.</p>

<p>Reply if you''re interested! Looking forward to seeing fellow RIVOT riders there.</p>

<p><strong>What to bring:</strong></p>
<ul>
<li>Fully charged scooter</li>
<li>Helmet and safety gear</li>
<li>Water and snacks</li>
<li>Emergency contact numbers</li>
</ul>',
    'Planning a group ride to Nandi Hills this Saturday. We''ll start from MG Road at 6 AM. Looking forward to seeing fellow RIVOT riders...',
    'TravelerJoe',
    4,
    'fas fa-road',
    8,
    187
),
(
    'Classic vs Pro - Which one for city commuting?',
    '<p>I''m looking to buy my first electric scooter and commute about 25km daily in Bangalore traffic. Should I go for the Classic or upgrade to the Pro?</p>

<p><strong>My daily routine:</strong></p>
<ul>
<li>Home to office: 12.5km each way</li>
<li>Mostly city roads with some highway</li>
<li>Budget is flexible but want value for money</li>
<li>Need reliable performance in traffic</li>
</ul>

<p>I''ve test driven both models and love them, but I''m confused about which one would be better for my use case. The Pro has better range and power, but is it worth the extra cost for my needs?</p>

<p>Current RIVOT owners, please share your experiences! Particularly interested in:</p>
<ul>
<li>Real-world range in city conditions</li>
<li>Comfort during long rides</li>
<li>Maintenance costs</li>
<li>Resale value</li>
</ul>',
    'I''m looking to buy my first electric scooter and commute about 25km daily in Bangalore traffic. Should I go for the Classic or upgrade to Pro...',
    'CityCommuter',
    2,
    'fas fa-motorcycle',
    24,
    421
),
(
    'DIY: Installing additional storage compartment',
    '<p>Here''s a step-by-step guide on how I added a custom storage compartment under the seat of my Max model. It''s perfect for carrying tools, documents, and small items.</p>

<p><strong>Materials needed:</strong></p>
<ul>
<li>Waterproof storage box (150mm x 100mm x 50mm)</li>
<li>Mounting brackets</li>
<li>Screws and washers</li>
<li>Rubber gasket</li>
<li>Basic tools</li>
</ul>

<p><strong>Installation steps:</strong></p>
<ol>
<li>Remove the seat carefully</li>
<li>Identify mounting points that don''t interfere with existing components</li>
<li>Mark and drill pilot holes</li>
<li>Install mounting brackets</li>
<li>Attach storage box with gasket for waterproofing</li>
<li>Test fit and reassemble</li>
</ol>

<p>Total cost was around ₹800 and took about 2 hours to complete. The compartment is completely waterproof and doesn''t affect the scooter''s balance.</p>

<p>Photos and detailed measurements in the comments. Happy to answer any questions!</p>',
    'Here''s a step-by-step guide on how I added a custom storage compartment under the seat of my Max model. It''s perfect for carrying...',
    'ModMaster',
    3,
    'fas fa-tools',
    15,
    312
),
(
    'Warning: Avoid this service center in Koramangala',
    '<p>Had a terrible experience at the Koramangala service center. They kept my scooter for 3 weeks for a minor issue and returned it with additional problems.</p>

<p><strong>Issues faced:</strong></p>
<ul>
<li>Promised 2-day repair, took 3 weeks</li>
<li>Poor communication - had to call multiple times for updates</li>
<li>Returned with scratches on the body</li>
<li>Brake issue that wasn''t there originally</li>
<li>Overcharged for parts</li>
</ul>

<p><strong>Original problem:</strong> Minor electrical issue with headlight</p>
<p><strong>What they did:</strong> Apparently replaced entire wiring harness (unnecessary) and damaged brake assembly in the process</p>

<p>I''ve now taken it to the Electronic City service center and they fixed everything properly within 2 days at a fraction of the cost.</p>

<p><strong>Recommendation:</strong> If you''re in South Bangalore, drive the extra distance to Electronic City or Whitefield service centers. Much better service quality.</p>

<p>Has anyone else had similar experiences? We should maintain a list of good vs bad service centers for the community.</p>',
    'Had a terrible experience at the Koramangala service center. They kept my scooter for 3 weeks for a minor issue and returned it with...',
    'DisappointedRider',
    1,
    'fas fa-exclamation-triangle',
    32,
    567
),
(
    'Group purchase: Discounted accessories from official dealer',
    '<p>I spoke with the RIVOT accessories dealer in Indiranagar and they''re willing to offer group discounts if we can get 10+ people interested.</p>

<p><strong>Available items with group pricing:</strong></p>
<ul>
<li>Phone holders: ₹1,200 (usual ₹1,500)</li>
<li>Rear storage boxes: ₹2,800 (usual ₹3,500)</li>
<li>LED strip lights: ₹800 (usual ₹1,000)</li>
<li>Custom seat covers: ₹1,500 (usual ₹2,000)</li>
<li>Tool kits: ₹600 (usual ₹800)</li>
</ul>

<p>All items are genuine RIVOT accessories with full warranty. The dealer is also throwing in free installation for group orders.</p>

<p><strong>Process:</strong></p>
<ol>
<li>Reply with items you''re interested in</li>
<li>Once we have 10+ people, I''ll coordinate with dealer</li>
<li>Payment and pickup can be arranged individually</li>
<li>Installation scheduled for same day if desired</li>
</ol>

<p>This is a great opportunity to get genuine accessories at a discount. Please share with other RIVOT owners you know!</p>

<p><strong>Deadline:</strong> Need confirmations by next Friday (Jan 26th)</p>',
    'I spoke with the RIVOT accessories dealer in Indiranagar and they''re willing to offer group discounts if we can get 10+ people interested...',
    'DealHunter',
    6,
    'fas fa-shopping-cart',
    18,
    156
);

-- Update category statistics based on posts
UPDATE forum_categories fc
SET
    fc.updated_at = NOW()
WHERE fc.id IN (
    SELECT DISTINCT category_id FROM forum_posts
);