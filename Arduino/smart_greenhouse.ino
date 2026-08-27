#include <WiFi.h>
#include <HTTPClient.h>

const char* ssid = "Tiven07";
const char* password = "_______";

const char* serverURL =
  "http://10.32.241.4/smart_greenhouse/api.php";

void setup() {
  Serial.begin(115200);
  delay(1000);

  Serial.println("Connecting to WiFi...");

  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println();
  Serial.println("WiFi connected!");
  Serial.print("ESP32 IP: ");
  Serial.println(WiFi.localIP());
}

void loop() {

  if (WiFi.status() == WL_CONNECTED) {

    HTTPClient http;

    // Fake sensor values for testing
    float temperature = 39.5;
    int humidity = 90;
    int soil_moisture = 45;
    int light_intensity = 800;

    String url = String(serverURL) +
                 "?temperature=" + String(temperature) +
                 "&humidity=" + String(humidity) +
                 "&soil_moisture=" + String(soil_moisture) +
                 "&light_intensity=" + String(light_intensity);

    Serial.println();
    Serial.println("Sending data...");
    Serial.println(url);

    http.begin(url);

    int httpCode = http.GET();

    Serial.print("HTTP response code: ");
    Serial.println(httpCode);

    if (httpCode > 0) {
      Serial.println("Server response:");
      Serial.println(http.getString());
    }

    http.end();

  } else {
    Serial.println("WiFi disconnected!");
  }

  delay(5000);
}