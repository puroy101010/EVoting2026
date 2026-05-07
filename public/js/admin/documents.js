$(document).ready(function(){


    $('#filter_box select').chosen({width: '100%'});
    
    load_proxy_documents(true);



    $(document).on('click', '.btn-upload-proxy', function(){


        $('#upload_proxy_modal [name=account_key]').val($(this).closest('tr').attr('data-account-key'));
        
        $('#upload_proxy_modal').modal('show');


    })



    $(document).on('submit', '#upload_form', function(e){
			
		e.preventDefault();

		var formData = new FormData();
				
		formData.append('proxy_document', $('[name=proxy_doc]')[0].files[0]);
        formData.append('account_key', $('#upload_proxy_modal [name=account_key]').val())
		


		$.ajax({
	
			url: BASE_URL + 'admin/document/proxy/upload',
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


                    load_proxy_documents(false); 

                    $('#upload_proxy_modal').modal('hide');

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


    $(document).on('click', '#show_filter', function(){

        $(this).hide();

        $('#hide_filter').show();

        $('#filter_box').slideToggle();

    })


    $(document).on('click', '#hide_filter', function(){

        $(this).hide();

        $('#show_filter').show();

        $('#filter_box').slideToggle();


    })



    $(document).on('click', '#btn_filter_reset', function(){

        $('#filter_form')[0].reset();    

        $('#filter_form select').trigger('chosen:updated');

        load_proxy_documents(true);

    })


    $(document).on('keyup', '#filter_form #search', function(){

        load_proxy_documents(true);

    })


    $(document).on('change', '#filter_form select', function(){

        load_proxy_documents($(this).hasClass('active-page') ? false : true);

    })


    $(document).on('submit', '#filter_form', function(e){

        e.preventDefault();

        // load_proxy_documents();

    })

    $(document).on('click', '.proxy-download-count', function(){


        let accountKey = $(this).closest('tr').attr('data-account-key');


        $.ajax({
            url: BASE_URL + 'admin/proxy/documents/download/history',
            method: 'GET', 
            dataType: 'json', 
            data: {
                account_key: accountKey 
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
                                        '<td>' + history['proxyFormNo'] + '</td>' + 
                                        '<td>' + history['createdAt'] + '</td>' +
                                    '</tr>';
                                        

                    }

                    histories = data.length === 0 ? '<tr><td class="text-center text-muted" colspan="4">No data</td></tr>' : histories

                    $('#proxy_download_history tbody').html(histories);

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


        $('#proxy_download_history').modal('show');

    })


    $(document).on('click', '.proxy-uploads', function(){

    

        ProxyDocument['accountKey'] = $(this).closest('tr').attr('data-account-key');

        
        load_proxy_uploads();

    })


    $(document).on('click', '.checkbox-verified', function(e){

        e.preventDefault();
      
        let uploadId    = $(this).closest('tr').attr('data-id');
        let action      = $(this).is(':checked');

        $.ajax({

            url: BASE_URL + 'admin/proxy/document/uploads/verify', 
            method: 'POST', 
            dataType: 'json',
            data: {
                id: uploadId, 
                action: action
            }, 

            statusCode: {

                200: function(data){    

                    load_proxy_uploads();

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

    $(document).on('click', '.btn-next', function(){

        let activePage = parseInt($('#filter_form [name=active_page]').children('option:selected').val());

        if($('#filter_form [name=active_page] [value='+(activePage + 1)+']').length > 0) {

            $('#filter_form [name=active_page] [value='+(activePage + 1)+']').attr('selected', true).siblings('selected', false);

            load_proxy_documents(false);

        }

    });

    $(document).on('click', '.btn-prev', function(){

        let activePage = parseInt($('#filter_form [name=active_page]').children('option:selected').val());

        if($('#filter_form [name=active_page] [value='+(activePage - 1)+']').length > 0) {

            $('#filter_form [name=active_page] [value='+(activePage - 1)+']').attr('selected', true).siblings('selected', false);

            load_proxy_documents(false);

        }

    });


    $(document).on('click', '.btn-allow-disallow-proxy-download', function(){

        let accountKey = $(this).closest('tr').attr('data-account-key');
        let action      = $(this).text() === 'Yes' ? 0 : 1;

        $.ajax({

            url: BASE_URL + 'proxy/documents/download/allow',
            dataType: 'json', 
            method: 'POST', 
            data: {account_key: accountKey, action: action},
            statusCode: {

                200: function(data){

                    load_proxy_documents(true);

                    Swal.fire({
                        icon: 'success',  title: 'Success', text: data.message
    
                        
                      })
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

})





function load_proxy_documents(reset = false) {



    if(reset === true) {

        $('#filter_form [name=active_page]').html('<option value="1">1</option>').trigger('chosen:updated');

    }



    let data = $('#filter_form').serialize();

    $.ajax({

        url:    BASE_URL + 'admin/proxy/documents/load', 
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
                    let allowProxyDownload  = member['allowProxyDownload'] === 1 ? '<span class="badge badge-mid-green e-currsor btn-allow-disallow-proxy-download">Yes</span>' : '<span class="badge badge-dark-green e-cursor btn-allow-disallow-proxy-download">No</span>';
                    let status              = member['role'] === 'stockholder' ? '---' : (member['isDelinquent'] === 1 ? '<span class="badge badge-dark-green">Delinquent</span>' : '<span class="badge badge-mid-green">Active</span>');

                    members += '<tr data-account-key="'+member['accountKey']+'"> ' + 
                                '<td class="td-padding">'+counter+'</td>' +
                                '<td class="td-padding">'+ (member['stockholder'] === null ? '' : member['stockholder']) +'</td>' +
                                '<td class="td-padding">'+accountNo+'</td>' +
                                '<td class="text-nowrap td-padding">'+(member['role'] === 'stockholder' ? '----' : '<a target="_blank" href="'+BASE_URL+'admin/proxy/form/download/'+member['accountKey']+'" style="color: #304c40;">' + member['proxyFormNo'] + ' &nbsp<i class="fa fa-download btn-download-proxy" aria-hidden="true"></i></span>')+'</td>' +
                                '<td class="td-padding"><span class="proxy-download-count e-cursor">'+member['downloadCount']+'</span></td>' + 
                                '<td class="td-padding text-center">'+allowProxyDownload+'</td>' + 
                                '<td class="td-padding"><a href="#" class="e-cursor proxy-uploads" style="color: #304c40;">View '+ (member['uploadCount'] === 0 ? '' : '<span class="badge badge-mid-green">'+member['uploadCount']+'</span>') + '</a></td>' +
                                '<td class="td-padding text-center">'+ status +'</td>' + 
                                '<td class="text-center"><button class="btn btn-mid-green btn-upload-proxy">Upload</button></td>' + 
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

                $('#memberTable tbody').html(data['data'].length === 0 ? '<tr><td class="text-center" colspan="7">No data</td></tr>' : members);

            
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

function load_proxy_uploads(){

    $.ajax({

        url: BASE_URL + 'admin/proxy/document/uploads/load', 
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

                    // let action = upload['status']


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




                    uploads += '<tr data-id="'+upload['proxyDocId']+'">' +
                                '<td class="td-padding"><a href="'+BASE_URL + 'admin/document/uploads/view/proxy/'+upload['proxyDocId']+'" target="_blank">'+filename+'</a></td>' + 
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



                
                uploads = data.length === 0 ? '<tr><td colspan="8" class="text-center text-muted">No data</td></tr>' : uploads;


                $('#proxy_uploads_modal tbody').html(uploads);

                $('#proxy_uploads_modal').modal('show');


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