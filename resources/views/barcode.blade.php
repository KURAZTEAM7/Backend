<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Barcode Scanner</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/quagga/0.12.1/quagga.min.js"></script>

  <style>
    * {
      padding: 0;
      margin: 0;
    }

    body {
      overflow: hidden;
    }

    video {
      width: 100vw;
      height: 100vh;
      background-color: #111;
    }

    .loader {
      z-index: 1000;
      display: inline-flex;
      gap: 10px;
      position: fixed;
      left: 50%;
      top: 50%;
    }

    .loader:before,
    .loader:after {
      content: "";
      height: 20px;
      aspect-ratio: 1;
      border-radius: 50%;
      background:
        radial-gradient(farthest-side, #000 95%, #0000) 35% 35%/6px 6px no-repeat #fff;
      animation: l5 3s infinite;
    }

    @keyframes l5 {

      0%,
      11% {
        background-position: 35% 35%
      }

      14%,
      36% {
        background-position: 65% 35%
      }

      38%,
      61% {
        background-position: 65% 65%
      }

      64%,
      86% {
        background-position: 35% 65%
      }

      88%,
      100% {
        background-position: 35% 35%
      }
    }
  </style>
</head>

<body>
  <div id="loader" class="loader" style="display: none"></div>
  <div id="interactive" style="width: 100%; height: 400px;"></div>
  <script>
    // Initialize Quagga
    console.log("Starting Quagga...");
    Quagga.init({
      inputStream: {
        name: "Live",
        type: "LiveStream",
        target: document.getElementById('interactive')
      },
      decoder: {
        readers: [
          "upc_reader",
          "ean_reader",
        ]
      }
    }, function (err) {
      if (err) {
        console.error("Quagga initialization error:", err);
        return;
      }
      console.log("Initialization finished. Ready to start");
      Quagga.start();
    });


    // On detected barcode
    Quagga.onDetected(function (data) {
      // alert(data.codeResult.code);
      let barcode = data.codeResult.code;
      console.log("Barcode detected: " + barcode);
      // fetch(`/api/product/search?barcode=${barcode}`);
      document.getElementById('loader').style.display = 'inline-flex';
      Quagga.stop();
      fetch(`/api/product/search?barcode=${barcode}`)
        .then(res => {
          // Check if response is OK (status 200-299), otherwise throw an error
          if (!res.ok) {
            throw new Error('Network response was not ok');
          }
          return res.json(); // Parse JSON if the response is OK
        })
        .then(response => {
          if (response && response.url)
            window.location.href = response.url; // Redirect if the URL exists in the response
          else
            throw new Error('No URL found in the response.');
        })
        .catch(error => {
          console.error('Fetch error:', error);
          document.getElementById('loader').style.display = 'none';
          alert('product is not found! try again.')
          Quagga.init({
            inputStream: {
              name: "Live",
              type: "LiveStream",
              target: document.getElementById('interactive')
            },
            decoder: {
              readers: [
                "upc_reader",
                "ean_reader",
              ]
            }
          }, function (err) {
            if (err) {
              console.error("Quagga initialization error:", err);
              return;
            }
            console.log("Initialization finished. Ready to start");
            Quagga.start();
          })
        })
        .finally(() => {
          console.log('Fetch operation complete.');
        });

    });
  </script>
</body>

</html>