// Footer

// Quand on clique sur le bouton "Retrouvez-nous" dans le footer, on active ou désactive la classe permettant d'afficher les boutons des réseaux sociaux
 $(".button").click(function(){
    $(".social.twitter").toggleClass("clicked");
    $(".social.facebook").toggleClass("clicked");
    $(".social.instagram").toggleClass("clicked");
    $(".social.youtube").toggleClass("clicked");
});


// Système de likes

// Fonction permettant de gérer les likes
function onClickBtnLike(event){

    // On empêche le chargement de la page de like, créée dans le ArticleController
    event.preventDefault();

    // On récupère l'url
    let url = this.href;

    // On sélectionne le span contenant le nombre de likes...
    let spanCount = this.querySelector(".js-likes");
    // ... ainsi que l'icône du pouce en l'air
    let icone = this.querySelector("i");

    // Grâce au bundle axios, installé en CDN dans ArticleList.hmtl.twig...
    axios.get(url).then(function(response){
        // ...on remplace l'ancien nombre de likes par le nouveau nombre de likes incrémenté ou décrémenté dans le tableau JSON.
        spanCount.textContent = response.data.likes;

        // S'il y a l'icône du pouce rempli...
        if(icone.classList.contains('fas')){
            // ... on la remplace par le pouce vide
            icone.classList.replace('fas', ('far'));
        // mais si ce n'est pas le pouce rempli...
        } else {
            // ... alors on l'affiche
            icone.classList.replace('far', ('fas'));
        }

    }).catch(function(error){

        // Si l'utilisateur n'est pas connecté...
        if(error.response.status === 403){
            // ... on affiche une erreur dans une fenêtre d'alerte
            window.alert("Il faut être connecté pour liker un article !");
        // ...sinon, pour n'importe quelle autre erreur, on affiche un autre alert
        } else {
            window.alert("Une erreur s'est produite, veuillez réessayer plus tard.");
        }
    });
}

// On sélectionne les boutons de like et on leur ajoute un écouteur d'évènement au clic, en appelant la fonction qu'on a créé ci-dessus
document.querySelectorAll(".js-like").forEach(function(link){
    link.addEventListener('click', onClickBtnLike);
});


// Page de profil

// Quand on clique sur le bouton "Changer la photo de profil"...
$(".change-photo").click(function(event){
    // ... il disparait...
    $(this).css('display', 'none');
    // ... et on affiche le formulaire
    $('.form-appear').removeClass('d-none');
});