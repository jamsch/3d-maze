String.prototype.capitalize = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
};

function mapRange(value, low1, high1, low2, high2){
    return low2 + (high2 - low2) * ((value - low1) / (high1 - low1));
}

//returns the size of an associative array
function getAssocSize(array) {
    var size = 0, key;
    for (key in array) {
        if (array.hasOwnProperty(key)) size++;
    }
    return size;
}

function zeroPad(n, numbDigits) {
  z = '0';
  n = n + '';
  return n.length >= numbDigits ? n : new Array(numbDigits - n.length + 1).join(z) + n;
}

function dist( point1, point2 ) {
  var xs = 0;
  var ys = 0;
  
  xs = point2.x - point1.x;
  xs = xs * xs;
 
  ys = point2.y - point1.y;
  ys = ys * ys;
  
  return Math.sqrt( xs + ys );
}

/**
 * Displays an element by fading in and out
 * @param element jQuery DOM object
 * @param speed integer in milliseconds
 * @param millisToStayOn integer
 */
function throb(element, speed, millisToStayOn) {

    element.fadeIn(speed, function(){
        if (typeof millisToStayOn === 'undefined') {
            element.fadeOut(speed, function() {throb(element, speed, millisToStayOn)});
            return;
        }
        setTimeout(function(){
            element.fadeOut(speed, function(){throb(element, speed, millisToStayOn)})
        }, millisToStayOn);
    });
}