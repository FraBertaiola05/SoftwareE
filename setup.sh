#!/bin/bash
sudo cp -r /home/berta/Desktop/SoftwareEngenireing/D3/Implementation /opt/lampp/htdocs/D3
sudo /opt/lampp/bin/mysql -u root < /opt/lampp/htdocs/D3/airport_with_data.sql
sudo /opt/lampp/bin/mysql -u root airport -e "UPDATE users SET password=sha2('Admin123!', 512) WHERE id=1"
echo ""
echo "Done! Open http://localhost/D3/index.php in your browser."
echo "Admin login: admin@admin.com / Admin123!"
