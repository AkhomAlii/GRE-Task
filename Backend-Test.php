<?php
    /**
     * Database Structure
     * 
     * Tables: [ customers, products, invoices, invoiceItems, ratings, blockedCountries ]
     * 
     * `customers` table columns: [ id, name, email, phone, countryCode, password, registration_date, ip_address ]
     *      e.g: [ 4, 'Ahmed Abdullah', 'ahmed@domain.com', '9640000000000', 'iq', '123asd', 1666313134, '168.68.01.33' ]
     * `products` table columns: [ id, name, description, image, available_units, price ]
     *      e.g: [ 2, 'iTunes $50 (US)', 'description...', '123.png', 2829, '60.99' ]
     * `invoices` table columns: [ id, customerID, items, total, date_created ]
     *      e.g: [ 66, 30, '283,91,82', '60.99', 1666313134 ]
     * `invoiceItems` table columns: [ id, productID, quantity, unit_price ]
     *      e.g: [ 93, 12, 2, '30.00' ]
     * `ratings` table columns: [ id, productID, customerID, stars, notes, date_created ]
     *      e.g: [ 3, 2, 4, 5, 'Great job.', 1666313134 ]
     * `blockedCountries` table columns: [ id, countryCode, date_created ]
     *      e.g: [ 7, 'il', 1666313134 ]
     */

    Class Main {

        protected PDO $pdo ;

        protected string $table = '';
        
        protected string $sql = '';

        protected $stmt  ;
        
        public function __construct(){
            if(! $this->pdo){
                $this->pdo = $this->db_connnect();
            }
        }


        /**
         * @return object
         */
        private function db_connnect(){
            $config['db'] = array(
                'host' => 'localhost',
                'user' => $_ENV['root'],
                'pass' => $_ENV[''],
                'name' => $_ENV['gre_task']
            );
            $conn = new PDO('mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['name'] , $config['db']['user'] , $config['db']['pass'], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"));
            $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $conn;
        }

        public function all($table = ''){
            $this->table = $table;
            
            $this->sql = "SELECT * FROM {$this->table}";
            
            return $this;
        }

        public function select($table, ...$cols){
            $this->table = $table;


            
            $this->sql = sprintf(
                "SELECT %s FROM (%s)",
                implode(', ', $cols),
                $this->table
            );            
            return $this;
        }

        public function where($column , $operator = '='){

            if($operator == 'IN'){
                
                $this->sql .= " WHERE {$column } {$operator} ( :{$column} )";

                return $this;    
            }
            $this->sql .= " WHERE {$column } {$operator} :{$column}";

            return $this;
        }



        public function exec($data = []){
            $this->stmt = $this->pdo->prepare($this->sql);
            $this->stmt->execute($data);
            return $this->stmt;
        }



        /**
         * Checks whether the customer's country code is blocked or not.
         * 
         * @param int $customerID
         * 
         * @return bool
         */
        public function isCustomerCountryBlocked($customerID){
            // Your code goes here


            if( ! $this->select('customers', 'country_code')->where('id')->exec(['id' => $customerID]) ){
               
                return false;
            }
            
            // $customer_country = $this->stmt->fetch(PDO::FETCH_ASSOC);

            return $this->select('blocked_countries', 'country_code')
                ->where('id')
                ->exec(['id' =>/* $customer_country*/   $this->stmt->fetch(PDO::FETCH_ASSOC)]) ;
        }




        /**
         * Lists the items of an invoice with full details of each item.
         * 
         * @param int $invoiceID
         * 
         * @return array
         */
        public function listInvoiceItems($invoiceID){
            // Your code goes here

            $items = $this->select('invoices', 'items' )
                ->where('id')
                ->exec(['id' => $invoiceID])
                ->fetch();

                return $this->all('invoice_items')
                    ->where('id', 'IN')
                    ->exec(['id' => $items])
                    ->fetchAll(PDO::FETCH_ASSOC);
        }




        /**
         * Get the most rated product and returns it's `id`.
         * 
         * @return int
         */
        public function getTopProduct(){
            // Your code goes here

            $result = $this->select('ratings', 'product_id', 'MAX(stars)')
            ->exec()
            ->fetchAll(PDO::FETCH_ASSOC);

            return $result['product_id'];
        }

        /**
         * Returns the products' price.
         * 
         * @param int $productID
         * 
         * @return float
         */
        public function getProductPrice($productID){
            // Your code goes here

            return $this->select('products', 'price')
                ->where('id')
                ->exec(['id' => $productID])
                ->fetch();
        }

        /**
         * Returns the total fee of the given product.
         * 
         * Notes & Hints:
         *  - The product price that's in the `products` table includes the fee.
         *  - The fee percentage is 15%.
         *  - The fee has to be subtracted from the `unit_price` column of the product.
         * 
         * @param int $productID
         * 
         * @return float
         */
        public function getProductFees($productID){
            // Your code goes here

            $product_price = $this->select('products', 'price')
                ->where('id')
                ->exec(['id' => $productID])
                ->fetch();
            
            return $product_price * 0.15 ;
        }

        /**
         * Returns the sales of the current year.
         * e.g: 10000.00 ($)
         * 
         * Hint: The fees should be subtracted.
         * 
         * @return float
         */
        public function getTotalSalesOfCurrentYear(){
            // Your code goes here
        }

        /**
         * Validates the customer email address.
         * 
         * @param string $email
         * 
         * @return bool
         */
        public function isCustomerEmailValid($email){
            // Your code goes here

            return filter_var($email, FILTER_VALIDATE_EMAIL);
        }

        /**
         * Used to filter the user inputs (SQL, XSS, etc)
         * 
         * @param string $input
         * 
         * @return string
         */
        public function filterUserInput($input){
            // Your code goes here

            return htmlspecialchars(stripcslashes($input)) ;
        }

    }
?>