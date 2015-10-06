function MobileNumberValidate() {
          var mobile = document.getElementById("mobile").value;
          var pattern = /^(((\+){0,1}91|0)(\s){0,1}(\-){0,1}(\s){0,1}){0,1}[7-9][0-9](\s){0,1}(\-){0,1}(\s){0,1}[1-9]{1}[0-9]{7}$/;
          if (pattern.test(mobile)) {
              return true;
          }
          alert("It is not valid mobile number, input 10 digits number!");
          return false;
      }
$( "form" ).submit(function( event ) {
  if ( Validate() ) {
  return;
}
  event.preventDefault();
});