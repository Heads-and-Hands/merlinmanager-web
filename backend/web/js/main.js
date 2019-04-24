function random() {
    let str = Math.random().toString(36).substring(2);
    document.getElementById("secret-input").value = str + str;
}