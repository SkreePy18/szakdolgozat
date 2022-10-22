function onChange(checkboxInstance, selector) {
    let cb = document.querySelector("#" + checkboxInstance);
    let pointSelector = document.getElementById(selector);
    var displayStyle = 'none';
    if(cb.checked) {
        displayStyle = 'block';
    }
  
    pointSelector.style.display = displayStyle;
  }