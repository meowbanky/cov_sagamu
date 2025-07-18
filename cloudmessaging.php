<html>
<title>Firebase Messaging Demo</title>
<style>
    div {
        margin-bottom: 15px;
    }
</style>
<body>
    <div id="token"></div>
    <div id="msg"></div>
    <div id="notis"></div>
    <div id="err"></div>
    <script src="https://www.gstatic.com/firebasejs/4.6.2/firebase.js"></script>
    <script>
        MsgElem = document.getElementById("msg")
        TokenElem = document.getElementById("token")
        NotisElem = document.getElementById("notis")
        ErrElem = document.getElementById("err")
        // Initialize Firebase
        // TODO: Replace with your project's customized code snippet
       

  // Initialize Firebase
  var config = {
    apiKey: "AIzaSyDjWfy534ATU3WYY92Lqvz2WLCDyF2jgHY",
    authDomain: "web-push-b43a3.firebaseapp.com",
    databaseURL: "https://web-push-b43a3.firebaseio.com",
    projectId: "web-push-b43a3",
    storageBucket: "web-push-b43a3.appspot.com",
    messagingSenderId: "923308278904"
  };
  firebase.initializeApp(config);


        const messaging = firebase.messaging();
        messaging
            .requestPermission()
            .then(function () {
                MsgElem.innerHTML = "Notification permission granted." 
                console.log("Notification permission granted.");

                // get the token in the form of promise
                return messaging.getToken()
            })
            .then(function(token) {
                TokenElem.innerHTML = "token is : " + token
            })
            .catch(function (err) {
                ErrElem.innerHTML =  ErrElem.innerHTML + "; " + err
                console.log("Unable to get permission to notify.", err);
            });

        messaging.onMessage(function(payload) {
            console.log("Message received. ", payload);
            NotisElem.innerHTML = NotisElem.innerHTML + JSON.stringify(payload) 
        });
    </script>

    <body>

</html>