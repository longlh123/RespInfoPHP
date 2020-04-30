var inputs = document.querySelectorAll('.inputfile');

Array.prototype.forEach.call(inputs, function(input){

    var label = input.nextElementSibling, labelVal = label.innerHTML;

    input.addEventListener('change', function(e){

        var pattern = /\\/;
        var fileName = e.target.value.split(pattern).pop();
        
        console.log(fileName);

        if(fileName)
            label.querySelector('span').innerHTML = fileName;
        else
            label = labelVal;
    });
});