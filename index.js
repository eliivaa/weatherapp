const cityInput = document.getElementById("cityInput");
const searchbtn = document.getElementById("button");
searchbtn.addEventListener('click', weatherdata);
const defaultCity = "dumfries&Ggalloway";

async function weatherdata() {
  const city = cityInput.value;
  const searchCity = city ? city : defaultCity


  try {
    const response = await fetch(`https://api.openweathermap.org/data/2.5/weather?units=metric&q=${searchCity}&appid=1536febfa54e587e5f1b046441a6810b`)
    const data = await response.json();
    console.log(data)

    const date = new Date(data.dt * 1000);
    console.log(date);

    const fDate = `${getWeekday(date)}, ${getMonth(date)} ${date.getDate()}, ${date.getFullYear()}`;
    console.log(fDate);

    function getWeekday(date) {
      const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
      return weekdays[date.getDay()];
    }

    function getMonth(date) {
      const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
      return months[date.getMonth()];
    }




    document.querySelector(".city").innerHTML = data.name;
    document.querySelector(".temp").innerHTML = Math.round(data.main.temp) + "&degC";
    document.querySelector(".press").innerHTML = data.main.pressure + 'Pa';
    document.querySelector(".hum").innerHTML = data.main.humidity + '%';

    document.querySelector(".wind").innerHTML = data.wind.speed + 'm/s';
    document.querySelector(".condition").src = `https://openweathermap.org/img/w/${data.weather[0].icon}.png`;
    document.querySelector(".max").innerHTML = Math.round(data.main.temp_max) + "&degC";
    document.querySelector(".dis").innerHTML = data.weather[0].description;

    document.getElementById("monthsection").innerHTML = fDate;



  } catch (error) {
    console.log(error);
    alert("City not found")
  }
}

document.addEventListener('DOMContentLoaded', weatherdata);
