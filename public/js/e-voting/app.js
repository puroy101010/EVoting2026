function EVoting(){

    this.html_decode = function (string) {

        return $("<textarea/>").html(string).val()
    }, 


    this.integer_to_roman = function (num) {

        
        var decimalValue = [1000, 900, 500, 400, 100, 90, 50, 40, 10, 9, 5, 4, 1];
        var romanNumeral = [
            "M",
            "CM",
            "D",
            "CD",
            "C",
            "XC",
            "L",
            "XL",
            "X",
            "IX",
            "V",
            "IV",
            "I"
        ];

        var romanized = "";

        for (var index = 0; index < decimalValue.length; index++) {
            while (decimalValue[index] <= num) {
                romanized += romanNumeral[index];
                num -= decimalValue[index];
            }
        }

        return romanized;
    }
    
}







$.ajaxSetup({
    headers: {

        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});





const SERVER_ERROR      = 'An internal error occured. If the problem persists, contact your administrator.';
const SESSION_TIMEOUT   = 'Session timeout. Please reload the page.';
const UNAUTHORIZED      = 'Unauthorized. Please login to continue.';
const FORBIDDEN         = 'Insufficient priveledge.';
const BAD_REQUEST       = '400: Bad Request';


EVoting = new EVoting();


// console.log(EVoting.integer_to_roman(4));


