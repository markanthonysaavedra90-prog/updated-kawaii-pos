function login(){
    let username = document.getElementById("username").value;
    let password = document.getElementById("password").value;

    fetch("login.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: `username=${username}&password=${password}`
    })
    .then(res => res.text())
    .then(role => {

        if(role === "admin"){
            window.location = "dashboard.html";
        } else if(role === "cashier"){
            window.location = "index.html";
        } else {
            alert("Invalid login");
        }

    });
}