function MobileNumberValidate() {
  var mobile = document.getElementById("mobile").value;
  var pattern = /^(((\+){0,1}91|0)(\s){0,1}(\-){0,1}(\s){0,1}){0,1}[7-9][0-9](\s){0,1}(\-){0,1}(\s){0,1}[1-9]{1}[0-9]{7}$/;
  if (pattern.test(mobile)) {
    return true;
  }
  alert("It is not valid mobile number, input 10 digits number!");
  return false;
}
$("form").submit(function(event) {
    if (Validate()) {
      return;
    }
    event.preventDefault();
  )
};

// Get current year
var currentYear = new Date().getFullYear();

// Get timestamp for some other date
var otherDateTimestamp = Date.parse(new Date('15 Mar' + new Date().getFullYear())) / 1000;

// Get current timestamp in seconds
var currentTimestamp = jQuery.now() / 1000;

// Remove duplicate values from array
var unique = duplicateValues.filter(function(elem, index, self) {
  return index == self.indexOf(elem);
}).sort(); // sort() is sorting the array after removing duplicates