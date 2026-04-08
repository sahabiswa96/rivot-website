-- Blog management table
CREATE TABLE IF NOT EXISTS blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    excerpt TEXT NOT NULL,
    content TEXT NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    author VARCHAR(100) NOT NULL,
    status ENUM('draft', 'published') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert demo blog posts
INSERT INTO blogs (title, excerpt, content, image_url, author, status) VALUES
(
    'The Rise of Electric Mobility',
    'Discover how electric vehicles are transforming urban travel and why Rivot is leading the charge.',
    '<p>Electric vehicles are revolutionizing the way we think about transportation. In India, the shift towards electric mobility is not just a trend—it''s a necessity driven by environmental concerns and rising fuel costs.</p>

<p>Rivot Motors has been at the forefront of this transformation, developing cutting-edge electric vehicles that combine performance, sustainability, and style. Our commitment to innovation has made us a leader in the electric vehicle space.</p>

<h3>Why Electric?</h3>
<p>Electric vehicles offer numerous advantages over traditional fuel-powered vehicles:</p>
<ul>
<li>Zero emissions for cleaner air</li>
<li>Lower operating costs</li>
<li>Instant torque and smooth acceleration</li>
<li>Reduced noise pollution</li>
</ul>

<p>As India moves towards a more sustainable future, Rivot is proud to be driving this change, one electric ride at a time.</p>',
    'Story_page/23.webp',
    'Rivot Team',
    'published'
),
(
    'Top 5 Group Ride Destinations',
    'Explore the best scenic routes for group rides with your Rivot community.',
    '<p>Nothing beats the thrill of exploring new destinations with fellow Rivot enthusiasts. Here are our top 5 recommended group ride destinations that offer breathtaking views and unforgettable experiences.</p>

<h3>1. Bangalore to Nandi Hills</h3>
<p>A classic route for early morning rides, offering stunning sunrise views and challenging hill climbs. The 60km journey is perfect for testing your Rivot''s range and performance.</p>

<h3>2. Mumbai Coastal Drive</h3>
<p>Experience the beauty of the Arabian Sea with this scenic coastal route. The Marine Drive to Bandra-Worli Sea Link stretch is ideal for evening group rides.</p>

<h3>3. Delhi to Agra</h3>
<p>A historic journey that takes you to the iconic Taj Mahal. This 200km route is perfect for weekend getaways with your riding group.</p>

<h3>4. Pune to Lonavala</h3>
<p>Navigate through the Western Ghats and enjoy the lush greenery and pleasant weather, especially during monsoons.</p>

<h3>5. Chennai ECR Route</h3>
<p>The East Coast Road offers beautiful beach views and multiple stops for refreshments and photo opportunities.</p>

<p>Remember to always ride safely, follow traffic rules, and ensure your Rivot is fully charged before embarking on these adventures!</p>',
    'Story_page/22.webp',
    'Rivot Community',
    'published'
),
(
    'Tips for Maintaining Your Rivot',
    'Keep your ride in top shape with these essential maintenance tips for Rivot owners.',
    '<p>Proper maintenance is key to ensuring your Rivot performs at its best for years to come. Here are essential tips every Rivot owner should follow.</p>

<h3>Battery Care</h3>
<p>Your Rivot''s battery is its heart. Follow these guidelines:</p>
<ul>
<li>Charge regularly, even if not fully depleted</li>
<li>Avoid complete discharge whenever possible</li>
<li>Store in a cool, dry place</li>
<li>Use only Rivot-approved chargers</li>
</ul>

<h3>Tire Maintenance</h3>
<p>Proper tire care ensures safety and efficiency:</p>
<ul>
<li>Check tire pressure monthly</li>
<li>Inspect for wear and tear regularly</li>
<li>Rotate tires as recommended</li>
<li>Replace when tread depth is insufficient</li>
</ul>

<h3>Cleaning and Care</h3>
<p>Keep your Rivot looking its best:</p>
<ul>
<li>Clean regularly with a damp cloth</li>
<li>Avoid high-pressure water on electrical components</li>
<li>Polish the body to maintain shine</li>
<li>Store indoors when possible</li>
</ul>

<h3>Regular Servicing</h3>
<p>Visit authorized Rivot service centers for:</p>
<ul>
<li>Scheduled maintenance checks</li>
<li>Software updates</li>
<li>Performance optimization</li>
<li>Warranty-covered repairs</li>
</ul>

<p>Following these maintenance tips will ensure your Rivot continues to deliver the exceptional performance and reliability you expect.</p>',
    'Story_page/21.webp',
    'Rivot Service Team',
    'published'
);