const API = "api";

function money(n){ return "RM " + Number(n).toFixed(2); }
function escapeHtml(value){
  return String(value ?? "").replace(/[&<>"']/g,m=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"}[m]));
}

async function getMovies(){
  const response = await fetch(`${API}/movies.php`, {cache:"no-store"});
  const data = await response.json();
  if(!response.ok || !data.success) throw new Error(data.message || "Unable to load movies.");
  return data.movies.map(m=>({
    id:Number(m.movie_id), title:m.title, genre:m.genre,
    duration:Number(m.duration_minutes), price:Number(m.ticket_price),
    image:m.image_url || "images/cinema.jpg"
  }));
}

async function renderMovies(){
  const grid=document.getElementById("movieGrid");
  if(!grid)return;
  grid.innerHTML="<p>Loading movies...</p>";
  try{
    const movies=await getMovies();
    if(!movies.length){grid.innerHTML="<p>No movies available.</p>";return;}
    grid.innerHTML=movies.map(m=>`
      <article class="movie-card">
        <img src="${escapeHtml(m.image)}" alt="${escapeHtml(m.title)}"
             onerror="this.src='images/cinema.jpg'">
        <div class="movie-info">
          <h3>${escapeHtml(m.title)}</h3>
          <p>Genre: ${escapeHtml(m.genre)}</p>
          <p>Duration: ${m.duration} minutes</p>
          <p class="movie-price">${money(m.price)}</p>
          <a class="btn" href="booking.html?movie=${m.id}">Book Now</a>
        </div>
      </article>`).join("");
  }catch(err){ grid.innerHTML=`<div class="message error">${escapeHtml(err.message)}</div>`; }
}

async function setupBooking(){
  const form=document.getElementById("bookingForm");
  if(!form)return;
  const message=document.getElementById("bookingMessage");
  let movies=[];
  try{ movies=await getMovies(); }
  catch(err){ message.innerHTML=`<div class="message error">${escapeHtml(err.message)}</div>`; return; }

  const select=document.getElementById("movieSelect");
  const params=new URLSearchParams(location.search);
  const selectedId=params.get("movie");
  select.innerHTML='<option value="">Select a movie</option>'+
    movies.map(m=>`<option value="${m.id}">${escapeHtml(m.title)} - ${money(m.price)}</option>`).join("");
  if(selectedId)select.value=selectedId;

  const date=document.getElementById("showDate");
  const today=new Date(); today.setMinutes(today.getMinutes()-today.getTimezoneOffset());
  date.min=today.toISOString().split("T")[0];

  function updateTotal(){
    const movie=movies.find(m=>m.id==select.value);
    const qty=Math.max(0,Number(document.getElementById("ticketQuantity").value)||0);
    document.getElementById("ticketPrice").textContent=movie?money(movie.price):money(0);
    document.getElementById("summaryQuantity").textContent=qty;
    document.getElementById("totalPrice").textContent=movie?money(movie.price*qty):money(0);
  }
  select.addEventListener("change",updateTotal);
  document.getElementById("ticketQuantity").addEventListener("input",updateTotal);
  updateTotal();

  form.addEventListener("submit",async e=>{
    e.preventDefault();
    message.innerHTML="";
    const movie=movies.find(m=>m.id==select.value);
    const qty=Number(document.getElementById("ticketQuantity").value);
    const name=document.getElementById("customerName").value.trim();
    const email=document.getElementById("customerEmail").value.trim();
    const showDate=date.value;
    const showTime=document.getElementById("showTime").value;

    if(!movie || !name || !email || !showDate || !showTime || qty<1 || qty>10){
      message.innerHTML='<div class="message error">Please complete all booking details correctly.</div>';
      return;
    }
    try{
      const response=await fetch(`${API}/bookings.php`,{
        method:"POST",headers:{"Content-Type":"application/json"},
        body:JSON.stringify({customerName:name,customerEmail:email,movieId:movie.id,showDate,showTime,quantity:qty})
      });
      const data=await response.json();
      if(!response.ok || !data.success) throw new Error(data.message||"Booking failed.");
      sessionStorage.setItem("lastBooking",JSON.stringify(data.booking));
      location.href=`confirmation.html?id=${data.booking.bookingId}`;
    }catch(err){
      message.innerHTML=`<div class="message error">${escapeHtml(err.message)}</div>`;
    }
  });
}

async function showConfirmation(){
  const box=document.getElementById("confirmationDetails");
  if(!box)return;
  box.innerHTML="<p>Loading booking...</p>";
  const id=new URLSearchParams(location.search).get("id");
  try{
    let b;
    if(id){
      const response=await fetch(`${API}/booking_get.php?id=${encodeURIComponent(id)}`,{cache:"no-store"});
      const data=await response.json();
      if(!response.ok || !data.success) throw new Error(data.message||"Booking not found.");
      b=data.booking;
    }else{
      b=JSON.parse(sessionStorage.getItem("lastBooking")||"null");
    }
    if(!b){box.innerHTML="<p>No booking information found.</p>";return;}
    box.innerHTML=`
      <p><strong>Booking ID:</strong> ${escapeHtml(b.bookingId)}</p>
      <p><strong>Name:</strong> ${escapeHtml(b.customerName)}</p>
      <p><strong>Email:</strong> ${escapeHtml(b.customerEmail)}</p>
      <p><strong>Movie:</strong> ${escapeHtml(b.movie)}</p>
      <p><strong>Date:</strong> ${escapeHtml(b.showDate)}</p>
      <p><strong>Time:</strong> ${escapeHtml(b.showTime)}</p>
      <p><strong>Tickets:</strong> ${escapeHtml(b.quantity)}</p>
      <p><strong>Total:</strong> ${money(b.total)}</p>`;
  }catch(err){box.innerHTML=`<div class="message error">${escapeHtml(err.message)}</div>`;}
}

document.addEventListener("DOMContentLoaded",()=>{
  renderMovies();
  setupBooking();
  showConfirmation();
});
