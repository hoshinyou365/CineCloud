let editingId = null;

function adminMoney(n){return "RM "+Number(n).toFixed(2);}
function adminEscape(v){return String(v ?? "").replace(/[&<>"']/g,m=>({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"}[m]));}
function message(text,type="success"){
 const el=document.getElementById("adminMessage");
 el.innerHTML=`<div class="message ${type}">${adminEscape(text)}</div>`;
 setTimeout(()=>el.innerHTML="",3000);
}

async function apiJson(url,options={}){
 const response=await fetch(url,options);
 const data=await response.json();
 if(!response.ok || !data.success) throw new Error(data.message||"Request failed.");
 return data;
}

async function getAdminMovies(){
 const data=await apiJson("api/movies.php",{cache:"no-store"});
 return data.movies.map(m=>({
  id:Number(m.movie_id),title:m.title,genre:m.genre,
  duration:Number(m.duration_minutes),price:Number(m.ticket_price),
  image:m.image_url||""
 }));
}

async function renderTable(){
 const tbody=document.getElementById("movieTableBody");
 tbody.innerHTML='<tr><td colspan="6">Loading...</td></tr>';
 try{
  const movies=await getAdminMovies();
  tbody.innerHTML=movies.length?movies.map(m=>`
   <tr>
    <td>${m.id}</td><td>${adminEscape(m.title)}</td><td>${adminEscape(m.genre)}</td>
    <td>${m.duration} min</td><td>${adminMoney(m.price)}</td>
    <td><div class="action-buttons">
      <button class="btn-edit" onclick="editMovie(${m.id})">Edit</button>
      <button class="btn-danger" onclick="deleteMovie(${m.id})">Delete</button>
    </div></td>
   </tr>`).join(""):`<tr><td colspan="6">No movies found.</td></tr>`;
 }catch(err){tbody.innerHTML=`<tr><td colspan="6">${adminEscape(err.message)}</td></tr>`;}
}

function resetForm(){
 editingId=null;
 document.getElementById("movieForm").reset();
 document.getElementById("movieId").value="";
 document.getElementById("formTitle").textContent="Add Movie";
 document.getElementById("saveButton").textContent="Add Movie";
 document.getElementById("cancelButton").classList.add("hidden");
}

async function editMovie(id){
 try{
  const movie=(await getAdminMovies()).find(m=>m.id===id);
  if(!movie)return;
  editingId=id;
  document.getElementById("movieId").value=id;
  document.getElementById("movieTitle").value=movie.title;
  document.getElementById("movieGenre").value=movie.genre;
  document.getElementById("movieDuration").value=movie.duration;
  document.getElementById("moviePrice").value=movie.price;
  document.getElementById("movieImage").value=movie.image;
  document.getElementById("formTitle").textContent="Edit Movie";
  document.getElementById("saveButton").textContent="Update Movie";
  document.getElementById("cancelButton").classList.remove("hidden");
  window.scrollTo({top:0,behavior:"smooth"});
 }catch(err){message(err.message,"error");}
}

async function deleteMovie(id){
 if(!confirm("Delete this movie?"))return;
 try{
  await apiJson("api/movie_delete.php",{
   method:"POST",headers:{"Content-Type":"application/json"},
   body:JSON.stringify({id})
  });
  await renderTable(); message("Movie deleted successfully.");
 }catch(err){message(err.message,"error");}
}

document.addEventListener("DOMContentLoaded",()=>{
 renderTable();
 document.getElementById("movieForm").addEventListener("submit",async e=>{
  e.preventDefault();
  const payload={
   id:editingId,
   title:document.getElementById("movieTitle").value.trim(),
   genre:document.getElementById("movieGenre").value.trim(),
   duration:Number(document.getElementById("movieDuration").value),
   price:Number(document.getElementById("moviePrice").value),
   image:document.getElementById("movieImage").value.trim()
  };
  if(!payload.title||!payload.genre||payload.duration<1||payload.price<=0){
   message("Please enter valid movie details.","error"); return;
  }
  try{
   const url=editingId?"api/movie_update.php":"api/movie_create.php";
   await apiJson(url,{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify(payload)});
   await renderTable();
   message(editingId?"Movie updated successfully.":"Movie created successfully.");
   resetForm();
  }catch(err){message(err.message,"error");}
 });
 document.getElementById("cancelButton").addEventListener("click",resetForm);
});
