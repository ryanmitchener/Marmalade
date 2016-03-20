// Marmalade JS library for some convenience functions
function Marmalade() {
    // Set up matches() abstraction
    if (document.body.matches !== undefined) {
        this.matchesProperty = "matches";
    } else if (document.body.webkitMatches !== undefined) {
        this.matchesProperty = "webkitMatches";
    } else if (document.body.msMatchesSelector !== undefined) {
        this.matchesProperty = "msMatchesSelector";
    }
}


// Checks if an element matches a given selector
Marmalade.prototype.matches = function(element, selector) {
    return element[this.matchesProperty](selector);
}


// Function for throttling certain events such as resize
// Taken from: https://developer.mozilla.org/en-US/docs/Web/Events/resize
Marmalade.prototype.throttleEvent = function(type, name, obj) {
    obj = obj || window;
    var running = false;
    var func = function() {
        if (running) { return; }
        running = true;
        requestAnimationFrame(function() {
            obj.dispatchEvent(new CustomEvent(name));
            running = false;
        });
    };
    obj.addEventListener(type, func);
};


// Find the closest ancestor to an element that matches a selector
// Optional limit parameter limits the amount of parents to traverse
Marmalade.prototype.parent = function(element, selector, limit) {
    limit = (limit - 1) || -1;
    element = element.parentElement;
    while (element !== null && !this.matches(element, selector)) {
        if (limit === 0) {
            return null;
        }
        element = element.parentElement;
        limit--;
    }
    return element;
}


// Create an instance of Marmalade
var marmalade = new Marmalade();