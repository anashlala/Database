document.addEventListener('DOMContentLoaded', function () {
    fetch( "me.php" )
    .then( response => {
        if ( !response.ok ) {
            throw new Error(response.status+" "+response.statusText)
        } else {
            return response.text();
        } 
    } )
    .then( data => document.getElementById( "student_info" ).innerHTML = data )
    .catch( error => document.getElementById( "student_info" ).innerHTML = '<strong>'+error+'</strong>' );

  const quoteContainer = document.getElementById('quoteContainer');
  const loadingIndicator = document.getElementById('loadingIndicator');
  let page = 1; // Initial page number
  let wait = 0; 

  function fetchQuotes() {
    wait = 1;
    let localPage = page;
    page++;
     fetch(`quotes.php?page=${localPage}`)
    .then(response => response.json())
    .then(data => {
      if (data.length > 0) {
        // Append quotes to the container
        data.forEach(quote => {
          quoteContainer.innerHTML += '<div class="col d-flex align-middle text-center">'+quote+'</div>';
          //quoteContainer.innerHTML += `<div class="mb-3">${quote.quote_text} - ${quote.author_name}</div>`;
        });
      } else {
        // No more quotes, remove the loading indicator
        loadingIndicator.style.display = 'none';
      }
    })
    .catch(error => {console.error('Error fetching quotes:', error);loadingIndicator.style.display = 'none';});
    wait = 0;
  }

  function handleScroll() {
    // Check if the user has scrolled to the bottom
    if (window.innerHeight + window.scrollY > document.body.offsetHeight - 100) {
      //loadingIndicator.style.display = 'block'; // Show loading indicator
      
      if( wait == 0 ) fetchQuotes(); // Fetch more quotes
    }
  }

  // Initial fetch on page load
  fetchQuotes();

  // Add scroll event listener
  window.addEventListener('scroll',
    (event) => {
        // handle scroll event
        handleScroll();
    }, 
    { passive: true }) ;
});

function buttonLogin( script, container_id, param_login, param_password, action ) {
  const formData = new FormData();
  formData.append("action", action);
  formData.append("username", param_login);
  formData.append("password", param_password);

  fetch( script , {
    method: "post",
    body: formData
  })   
  .then( response => {
    if ( !response.ok ) {
        throw new Error(response.status+" "+response.statusText)
    } else {
        return response.text();
    } 
  } )
  .then( data => document.getElementById( container_id ).innerHTML = data )
  .catch( error => document.getElementById( container_id ).innerHTML = '<strong>'+error+'</strong>' );
}
function buttonFav( button_id, check ) {
  const formData = new FormData();
  formData.append("action", "favourite");
  formData.append("quote", button_id);
  formData.append("check", check);


  fetch( "quotes.php" , {
    method: "post",
    body: formData
  })   
  .then( response => {
    if ( !response.ok ) {
        throw new Error(response.status+" "+response.statusText)
    } else {
        return response.text();
    } 
  } )
  .catch( error => document.getElementById( container_id ).innerHTML = '<strong>'+error+'</strong>' );
}