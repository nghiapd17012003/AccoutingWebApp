
<!-- ABOUT THE PROJECT -->
## About

<b>An accounting web application develope by leveraging existing open source repo (ITFlow) to match my company's needs</b>

*Please visit the original repo https://github.com/itflow-org/itflow for demo and further information.



### In Beta
* This project is in beta with many ongoing changes. Updates may unintentionally introduce bugs/security issues.
* Whilst we are confident the code is safe, nothing in life is 100% safe or risk-free. Use your best judgement before deciding to store highly confidential information in ITFlow.

<!-- BUILT WITH -->
### Built With

* Backend / PHP libs
  * PHP
  * MariaDB
  * PHPMailer
  * HTML Purifier
  * PHP Mime Mail Parser

* CSS
  * Bootstrap
  * AdminLTE
  * fontawesome

* JS Libraries
  * chart.js
  * moments.js
  * jQuery
  * pdfmake
  * Select2
  * SummerNote
  * FullCalendar.io

<!-- GETTING STARTED -->
## Getting Started / Installation

ITFlow is self-hosted. There is a full installation guide in the [docs](https://wiki.itflow.org/doku.php?id=wiki:installation), but the main steps are:

1. Install a LAMP stack (Linux, Apache, MariaDB, PHP)
   ```sh
   sudo apt install git apache2 php libapache2-mod-php php-intl php-imap php-mailparse php-mysqli php-curl mariadb-server
   ```  
2. Clone the repo
   ```sh
   git clone https://github.com/itflow-org/itflow.git /var/www/html
   ```
3. Create a MariaDB Database
4. Point your browser to your HTTPS web server to begin setup

<!-- FEATURES -->
## Key Features
* Client documentation - assets, contacts, domains, docs, files, passwords, and more 
* Accounting / Billing - finance dashboard, quotes, invoices, accounting, expenses, etc
* Client Portal - self service quote/invoice/ticket management for clients
* Alerting - account balance, invoices, domain/SSL renewals
* Completely free & open-source alternative to ITGlue and Hudu
