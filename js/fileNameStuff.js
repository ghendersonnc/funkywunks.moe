let img = document.getElementById('image-file');
let fileNameDiv = document.getElementById('file-name');
let fileNameSpan = document.getElementById('file-name-span');
img.onchange = (e) => {
    fileNameDiv.style.display = 'block';
    fileNameSpan.innerText = img.files[0].name;
    console.log(img.files);
}