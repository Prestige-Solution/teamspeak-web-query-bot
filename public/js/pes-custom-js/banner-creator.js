function addBannerOptionGroup()
{
    let clone = document.querySelector('#BannerOptionGroup').cloneNode(true);
    clone.querySelector('#BannerOption1').value = '1';
    clone.querySelector('#BannerOption2').value = '0';
    clone.querySelector('#Text').value ='';
    clone.querySelector('#CoordX').value = '';
    clone.querySelector('#CoordY').value = '';

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

    document.getElementById('Xcoord').value = x.toFixed(0);
    document.getElementById('Ycoord').value = y.toFixed(0);
}