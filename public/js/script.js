// Footer
$(".button").click(function(){
    $(".social.twitter").toggleClass("clicked");
    $(".social.facebook").toggleClass("clicked");
    $(".social.instagram").toggleClass("clicked");
    $(".social.youtube").toggleClass("clicked");
});

// Système de likes
function onClickBtnLike(event){
    event.preventDefault();
    let url = this.href;
    let spanCount = this.querySelector(".js-likes");
    let icone = this.querySelector("i");

    axios.get(url).then(function(response){
        spanCount.textContent = response.data.likes;

        if(icone.classList.contains('fas')){
            icone.classList.replace('fas', ('far'));
        } else {
            icone.classList.replace('far', ('fas'));
        }
    }).catch(function(error){
        if(error.response.status === 403){
            window.alert("Il faut être connecté pour liker un article !");
        } else {
            window.alert("Une erreur s'est produite, veuillez réessayer plus tard.");
        }
    });
}
document.querySelectorAll(".js-like").forEach(function(link){
    link.addEventListener('click', onClickBtnLike);
});

// Page de profil
$(".profil-pic").click(function(){
    $(".profil-form").toggleClass("d-none");
});