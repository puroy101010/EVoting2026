$(document).ready(function(){

    $('#filter_box select').chosen({width: '100%'});
    
    load_spa_documents(true);

    // 2021-09-02
    $(document).on('click', '#show_filter', function(){

        $(this).hide();

        $('#hide_filter').show();

        $('#filter_box').slideToggle();

    })

    // 2021-09-02
    $(document).on('click', '#hide_filter', function(){

        $(this).hide();

        $('#show_filter').show();

        $('#filter_box').slideToggle();


    })

    // 2021-09-02
    $(document).on('click', '#btn_filter_reset', function(){

        $('#filter_form')[0].reset();    

        $('#filter_form select').trigger('chosen:updated');

        load_spa_documents(true);

    })

    // 2021-09-02
    $(document).on('change', '#filter_form select', function(){

        load_spa_documents($(this).hasClass('active-page') ? false : true);

    })

    // 2021-09-02
    $(document).on('submit', '#filter_form', function(e){

        e.preventDefault();

    })

    // 2021-09-02
    $(document).on('click', '.spa-download-count', function(){

        let accountKey = $(this).closest('tr').attr('data-account-key');

        $.ajax({

            url: BASE_URL + 'admin/spa/documents/history',
            method: 'GET', 
            dataType: 'json', 
            data: {
                account_key: accountKey 
            },

            beforeSend: function(){

                $('#spa_download_history tbody').html('');

            },

            statusCode: {

                200: function(data){

                    let histories = '';
                    let accountType;

                    for(let history of data) {

                        switch(history['role']) {

                            case 'stockholder':
                                accountType = 'Stockholder';
    
                            break;
    
                            case 'corp-rep': 
                                accountType = 'Corp. Rep.';
    
                            break;
    
    
                            case 'admin':   
                                accountType = 'Admin';
    
                            break;

                            default: 
                                accountType = 'DEFAULT';
    
                            break;

                        }
    

                        histories += 
                                    '<tr>' + 
                                        '<td>' + history['stockholder'] + '</td>' + 
                                        '<td>' + accountType + '</td>' + 
                                        '<td>' + history['email'] + '</td>' + 
                                        '<td>' + history['createdAt'] + '</td>' +
                                    '</tr>';
                                        

                    }

                    histories = data.length === 0 ? '<tr><td class="text-center text-muted" colspan="4">No data</td></tr>' : histories

                    $('#spa_download_history tbody').html(histories);

                }, 

                400: function(data){
                    alert(data["responseJSON"]["message"]);
                },
    
                401: function(){
                    alert(UNAUTHORIZED);
                }, 
    
                403: function(){
                 alert(FORBIDDEN);
                }, 
    
                419: function(){
                    alert(SESSION_TIMEOUT);
                }, 
    
                500: function(){
                    alert(SERVER_ERROR);
                }
                
            }



        })


        $('#spa_download_history').modal('show');

    })


    // 2021-09-02
    $(document).on('click', '.spa-uploads', function(){

        ProxyDocument['accountKey'] = $(this).closest('tr').attr('data-account-key');

        load_spa_uploads();

    })

    // 2021-09-02

    $(document).on('click', '.checkbox-verified', function(e){

        e.preventDefault();
      
        let uploadId    = $(this).closest('tr').attr('data-id');
        let action      = $(this).is(':checked');

        $.ajax({

            url: BASE_URL + 'admin/spa/document/uploads/verify', 
            method: 'POST', 
            dataType: 'json',
            data: {
                id: uploadId, 
                action: action
            }, 
            

            statusCode: {

                200: function(data){    

                    load_spa_uploads();

                    alert(data.message);

                }, 


                400: function(data){
                    alert(data["responseJSON"]["message"]);
                },
    
                401: function(){
                    alert(UNAUTHORIZED);
                }, 
    
                403: function(){
                    alert(FORBIDDEN);
                }, 
    
                419: function(){
                    alert(SESSION_TIMEOUT);
                }, 
    
                500: function(){
                    alert(SERVER_ERROR);
                }


            }

        })
    })

    //done 2021-09-02
    $(document).on('click', '.btn-next', function(){

        let activePage = parseInt($('#filter_form [name=active_page]').children('option:selected').val());

        if($('#filter_form [name=active_page] [value='+(activePage + 1)+']').length > 0) {

            $('#filter_form [name=active_page] [value='+(activePage + 1)+']').attr('selected', true).siblings('selected', false);

            load_spa_documents(false);

        }

    });

    //done 2021-09-02
    $(document).on('click', '.btn-prev', function(){

        let activePage = parseInt($('#filter_form [name=active_page]').children('option:selected').val());

        if($('#filter_form [name=active_page] [value='+(activePage - 1)+']').length > 0) {

            $('#filter_form [name=active_page] [value='+(activePage - 1)+']').attr('selected', true).siblings('selected', false);

            load_spa_documents(false);

        }

    });


    //done 2021-09-02
    $(document).on('click', '.btn-upload-spa', function(){

        $('#upload_spa_modal').modal('show');

        $('#upload_form [name=id]').val($(this).closest('tr').attr('data-account-key'));


    })

     // done 2021-09-02

    $(document).on('submit', '#upload_form', function(e){
			
        e.preventDefault();
  
        var formData = new FormData();
            
        formData.append('spa_document', $('[name=spa_doc]')[0].files[0]);
  
        formData.append('account_key', $('#upload_form [name=id]').val());
  
        $.ajax({
  
          url: BASE_URL + 'admin/document/spa/upload',
          method: "POST",
          dataType: "json",
          contentType: false,
          cache: false,
          processData: false,
          data: formData,
  
          // this part is progress bar
          xhr: function () {
              var xhr = new window.XMLHttpRequest();
              xhr.upload.addEventListener("progress", function (evt) {
                if (evt.lengthComputable) {
                  var percentComplete = evt.loaded / evt.total;
                  percentComplete = parseInt(percentComplete * 100);
                  $('#myprogress').text(percentComplete + '%');
                  $('#myprogress').css('width', percentComplete + '%');
                }
              }, false);
              return xhr;
            },
  
            beforeSend: function() {
              $('#btn_upload').text("Uploading . . . ").attr('disabled', true);
            },
  
            complete: function(){
              $('#btn_upload').text("Upload").attr('disabled', false);
            },
  
  
            
            statusCode: {
  
              200: function(data){
  
  
                Swal.fire({icon: 'success', title: 'Success!', text: data.message}).then(() => {
                        location.reload();
                      })
  
  
              }, 
  
  
              400: function(data){
  
                Swal.fire({icon: 'info', title: 'Info', text: data["responseJSON"]["message"]});
            
              },
  
              401: function(){
                alert(UNAUTHORIZED);
              }, 
  
              403: function(){
                alert(FORBIDDEN);
              }, 
  
              419: function(){
  
                alert(SESSION_TIMEOUT);
  
              }, 
  
              500: function(){
                alert(SERVER_ERROR);
              }
  
              
              
              
            },
  
          }).done(function(){
            $('#btn_upload').text("Upload").attr('disabled', false);
          })
    })

})




//done 2021-09-02
function load_spa_documents(reset = false) {

    if(reset === true) {

        $('#filter_form [name=active_page]').html('<option value="1">1</option>').trigger('chosen:updated');

    }

    let data = $('#filter_form').serialize();

    $.ajax({

        url:    BASE_URL + 'admin/spa/documents/load', 
        method: 'GET',
        dataType: 'json', 
        data: data, 

        beforeSend: function(){

            $('#memberTable tbody').css('opacity', '0.1');

        }, 


        complete: function(){

            $('#memberTable tbody').css('opacity', '1');

        },

        statusCode: {

            200: function(data){

                let members = '';
                let counter = 1;


                for(member of data['data']) {


                    let accountNo           = member['role'] === 'stockholder' ? member['accountNo'] : member['accountNo'] + ' - ' + EVoting.integer_to_roman(member['suffix']); 
                    let allowSpaDownload    = member['allowSpaDownload'] === 1 ? '<span class="badge badge-mid-green e-currsor">Yes</span>' : '<span class="badge badge-dark-green e-cursor">No</span>';
                    let status              = member['role'] === 'stockholder' ? '---' : (member['isDelinquent'] === 1 ? '<span class="badge badge-dark-green">Delinquent</span>' : '<span class="badge badge-mid-green">Active</span>');
                    let downloadCount       = member['downloadCount'];
                    
                    
                    members += '<tr data-account-key="'+member['accountKey']+'" data-account-no="'+member['accountNo']+'"> ' + 
                                '<td class="td-padding">'+counter+'</td>' +
                                '<td class="td-padding">'+member['stockholder']+'</td>' +
                                '<td class="td-padding">'+accountNo+'</td>' +
                                '<td class="text-nowrap td-padding"><a target="_blank" href="'+BASE_URL+'admin/spa/form/download/'+member['accountKey']+'" style="color: #304c40;">&nbsp<i class="fa fa-download btn-download-proxy" aria-hidden="true"></i> &nbsp; Download</span></td>' +
                                '<td class="td-padding"><span class="spa-download-count e-cursor">'+downloadCount+'</span></td>' + 
                                '<td class="td-padding">'+(member['role'] === 'stockholder' ? '---' : allowSpaDownload)+'</td>' + 
                                '<td class="td-padding text-center"><a href="#" class="e-cursor spa-uploads" style="color: #304c40;">View '+ (member['uploadCount'] === 0 ? '' : '<span class="badge badge-secondary">'+member['uploadCount']+'</span>') + '</a></td>' +
                                '<td class="td-padding text-center">'+ status +'</td>' + 
                                '<td class="text-center"><button class="btn btn-mid-green btn-button btn-upload-spa">Upload</button></td>' + 
                            '</tr>';


                    counter++;

                }


                let pages = '';

                for(let i = 1; i <= data['total_page']; i++) {

                    pages += '<option value="'+i+'" '+(data['active_page'] == i ? 'selected' : '')+'>'+i+'</option>';

                }


                $('#active_page').html('Page '+data['active_page']+' of ' + data['total_page']);

                $('#filter_form [name=active_page]').html(pages).trigger('chosen:updated');

                $('#record_summary').html(data['record_summary']);

                $('#memberTable tbody').html(data['data'].length === 0 ? '<tr><td class="text-center td-padding" colspan="7">No data</td></tr>' : members);

            
            }, 


            400: function(data){

                alert(data["responseJSON"]["message"]);

            },

            401: function(){

                alert(UNAUTHORIZED);

            }, 

            403: function(){

             alert(FORBIDDEN);

            }, 

            419: function(){

                alert(SESSION_TIMEOUT);

            }, 

            500: function(){

                alert(SERVER_ERROR);

            }

        
        }

    })
}


// 2021-09-02
function load_spa_uploads(){

    $.ajax({

        url: BASE_URL + 'admin/spa/document/uploads/load', 
        method: 'GET', 
        dataType: 'json', 

        data: {account_key: ProxyDocument['accountKey']},

        statusCode: {

            200: function(data){

                let uploads = '';
                let accountType;
                let counter = 0;

                for(let upload of data) {

                    let filename = upload['origFilename'].length > 15 ? upload['origFilename'].substring(0, 12) + '...' + upload['origFilename'].split('.').pop() : upload['origFilename'];

                    switch(upload['role']) {

                        case 'stockholder':

                            accountType = 'Stockholder';


                        break;


                        case 'corp-rep': 

                            accountType = 'Corp. Rep.';

                        break;


                        case 'admin':   

                            accountType = 'Admin';

                        break;


                        default: 

                            accountType = 'DEFAULT';

                        break;
                    }




                    uploads += '<tr data-id="'+upload['spaDocId']+'">' +
                                '<td class="td-padding"><a href="'+BASE_URL + 'document/uploads/view/spa/'+upload['spaDocId']+'" target="_blank">'+filename+'</a></td>' + 
                                '<td class="td-padding">'+upload['stockholder']+'</td>' +
                                '<td class="td-padding">'+upload['email']+'</td>' +
                                '<td class="td-padding">'+accountType+'</td>' +
                                '<td class="td-padding">'+upload['createdAt']+'</td>' +
                                '<td class="td-padding">'+(upload['verifiedAt'] === null ? '---' : upload['verifiedAt'])+'</td>' +
                                '<td class="td-padding">'+(upload['verifiedBy'] === null ? '---' : upload['verifiedBy'])+'</td>' +
                                '<td class="text-nowrap"> <input type="checkbox" class="checkbox-verified" ' + (upload['isVerified'] === 1 ? 'checked' : '') +' id="proxy_upload_verified_'+counter+'"><label class="label-text" for="proxy_upload_verified_'+counter+'"> &nbsp; Validated</label></td>' +
                                '</tr>';


                    counter++;

                }

                
                uploads = data.length === 0 ? '<tr><td colspan="8" class="text-center text-muted td-padding">No data</td></tr>' : uploads;


                $('#spa_uploads_modal tbody').html(uploads);

                $('#spa_uploads_modal').modal('show');


            }, 

            400: function(data){
                alert(data["responseJSON"]["message"]);
            },

            401: function(){
                alert(UNAUTHORIZED);
            }, 

            403: function(){
             alert(FORBIDDEN);
            }, 

            419: function(){
                alert(SESSION_TIMEOUT);
            }, 

            500: function(){
                alert(SERVER_ERROR);
            }
        }
    })

    
}


let ProxyDocument = {};