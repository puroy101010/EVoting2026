<?php

    namespace App\Exports;


    use Maatwebsite\Excel\Concerns\FromArray;

    class StockholderExport implements FromArray
    {

        protected $stockholders;

        public function __construct(array $stockholders)
        {
            $this->stockholders = $stockholders;
        }

        public function array(): array {

            return $this->stockholders;
            
        }

      
    }

?>