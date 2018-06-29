jQuery(document).ready(function($) {


    $('.js-aw-referrals-preview-share-email').click(function(e){
        e.preventDefault();

        AutomateWoo.open_email_preview( 'referral_share', {} );
    });



    function revealBasedOnReferralType() {
        if ( $('#aw_referrals_type').val() === 'coupon' ) {
            $('#aw_settings_section_aw_referrals_offer').show();
            $('#aw_settings_section_aw_referrals_link').hide();
            $('#aw_referrals_reward_event_field_row').hide();
        }
        else {
            $('#aw_settings_section_aw_referrals_offer').hide();
            $('#aw_settings_section_aw_referrals_link').show();
            $('#aw_referrals_reward_event_field_row').show();
        }
    }


    function revealBasedOnRewardType() {
        if ( $('#aw_referrals_reward_type').val() === 'none' ) {
            $('#aw_referrals_reward_amount_field_row').hide();
        }
        else {
            $('#aw_referrals_reward_amount_field_row').show();
        }
    }



    $('#aw_referrals_type').change(function(){
       revealBasedOnReferralType();
    });

    $('#aw_referrals_reward_type').change(function(){
        revealBasedOnRewardType();
    });

    revealBasedOnReferralType();
    revealBasedOnRewardType();

});