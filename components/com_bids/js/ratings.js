function FillRatings(){
    var elems=document.getElementsByTagName('span');
    for (var i=0;i<elems.length;i++){

        if ($(elems[i]).hasClass('rating_user')){

            rating=parseFloat (elems[i].getAttribute("rating"));
            if (isNaN(rating)) rating=0;

            elems[i].innerHTML="";
            for (var j=0;j<Math.floor(rating/2);j++)
                elems[i].innerHTML+="<img src='"+JS_ROOT_HOST+"components/com_bids/images/f_rateit_1.png' id='auction_star' border=0>";
            if(rating/2>Math.floor(rating/2))
                elems[i].innerHTML+="<img src='"+JS_ROOT_HOST+"components/com_bids/images/f_rateit_h.png' id='auction_star' border=0>";
            for (var j=Math.ceil(rating/2);j<5;j++)
                elems[i].innerHTML+="<img src='"+JS_ROOT_HOST+"components/com_bids/images/f_rateit_0.png' id='auction_star' border=0>";

        }

    }

}