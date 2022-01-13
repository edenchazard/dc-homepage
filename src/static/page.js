function submit_form(){
    if(document.getElementById('filter_dropdown').value == ""){
        window.location = "/dc/prizes";
    }
    else{
        document.getElementById('filter_form').submit();
    }
}