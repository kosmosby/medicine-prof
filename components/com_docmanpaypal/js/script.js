jQuery(document).ready(function() {
	jQuery('.add-to-cart').click(function() {
	  addToCart(jQuery(this).attr('data-id'));
  });
});