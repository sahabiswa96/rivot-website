-- Create settings table
CREATE TABLE IF NOT EXISTS settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) NOT NULL UNIQUE,
  setting_value TEXT,
  description VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin email setting
INSERT INTO settings (setting_key, setting_value, description)
VALUES ('admin_email', 'parthait2003@gmail.com', 'Admin email address for receiving notifications')
ON DUPLICATE KEY UPDATE setting_value = setting_value;
