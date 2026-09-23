let url = window.location.href;
var arr = url.split('?');
console.log(url)
if (arr.length > 1 && arr[1] !== '') {
  alert("Sikeres belépés: "+ arr[1].split("=")[1]);
}