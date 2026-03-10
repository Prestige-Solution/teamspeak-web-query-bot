function addBannerOptionGroup()
{
    let clone = document.querySelector('#BannerOptionGroup').cloneNode(true);
    clone.querySelector('#option_id').value = '1';
    clone.querySelector('#extra_option').value = '0';
    clone.querySelector('#text').value ='';
    clone.querySelector('#coord_x').value = '';
    clone.querySelector('#coord_y').value = '';

    document.querySelector('#AddOptionGroupButton').before(clone);
}

function imageCoordinates()
{
    let img = document.getElementById('BannerImage');

    //get natural X coordinates
    let xView = img.width;
    let xNatural = img.naturalWidth;
    let scaleX = xNatural / xView;
    let mouseX = event.offsetX;
    let x = mouseX * scaleX;

    //get natural Y coordinates
    let yView = img.height;
    let yNatural = img.naturalHeight;
    let scaleY = yNatural / yView;
    let mouseY = event.offsetY;
    let y = mouseY * scaleY;

    document.getElementById('coord_x').value = x.toFixed(0);
    document.getElementById('coord_y').value = y.toFixed(0);
}
