function dorate(val)
{

	$$('.auctionRateFormRate').each(
        function(el,i){
        	el.value = val;
        }
	)

	$$('.rate_star').each(

        function(el,i){

            rate=el.getProperty('rate');

            if (rate<=val)

                el.src=el.src.replace('_0.png','_1.png');

            else

                el.src=el.src.replace('_1.png','_0.png');

        })

    

}

function showrate(image,val)

{

    if (!val)

    {

        frm=$$('[name=auctionRateForm]');

        frm.each(function(el,i){

            val=el.rate.value;

        });

    }

    stars=$$('.rate_star');

    stars.each(function(el,i){

        rate=el.getProperty('rate');

        if (rate<=val)

                el.src=el.src.replace('_0.png','_1.png');

        else

                el.src=el.src.replace('_1.png','_0.png');

    })

}