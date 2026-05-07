<html lang="en">
    <head>
        <meta charset="utf-8">
        <title><?php

use App\Http\Controllers\EApp;

echo $title; ?></title>
        <style>
            @page{

            
                margin: 30px;
                size: 216mm 279mm;
               
            }


            @font-face {
                font-family: 'Book Antiqua'; 
                src: url("{{ asset('fonts/Book Antiqua.ttf') }}");
                font-weight: regular;
            }  


            @font-face {
                font-family: 'Calibri'; 
                src: url("{{ asset('fonts/Calibri Regular.ttf') }}");
                font-weight: regular;
            }  


            body {
                font-family: "Book Antiqua";
                /* background-color: blue; */
            }


            .page-break {
            page-break-after: always;
            }

            .page-break:last-child {
            page-break-after: avoid;
            }

            p {
                text-align: justify; 
                font-size: 13px;
                /* line-height: 13px; */
            }

            .semi-title {

                font-size: 16px;
            }

            #document-header p {
                margin: 0px;
                padding: 0px; 

            }

            #document-header {

                text-align: center; 
                margin: 0px;
                padding: 0px;
                line-height: 12px;
            }

            #document-header .p-header-info {
                text-align: center;
            } 

            .title-header {
                font-weight: bold;
                line-height: 11px;
     
            } 


            table {
                border: 1px solid black; 
                border-collapse: collapse; 
            }

            table th, table td,{
                border: 1px solid black; 
                font-size: 12px;
            }

            table td {
                padding: 7px;
            }



            .font-calibri {
                font-family: Calibri;
            }
            
        </style>
    </head>
    <body>
        
        <div id="document-header">
            <P class="p-header-info">VALLEY GOLF & COUNTRY CLUB, INC.</P>
            <P class="p-header-info">ANNUAL GENERAL MEETING</P>
            <P class="p-header-info">September 25, 2022 at 4:00 P.M.</P>
            <P class="p-header-info"> Via Live Streaming,  VGCCI</P>
            <p class="p-header-info" style="margin-top: 17px;">PROXY</p>
            <p class="p-header-info">No. <?php echo '<u>' . $proxyFormNo . '-' . $downloadCounter; ?>-S-2022</u>  ACCT. No. <u><?php echo $accountNo; ?></u></p>

        </div>


        <p>I, <u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $stockholder; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u> the undersigned member of Valley Golf & Country Club, Inc. do hereby appoint, name and constitute:</p>

       <p style="text-align: center;">__________________________________</p>

        <p>or, in the absence and/or non-attendance of my PROXY the Chairman of the Meeting,( <span class="title-header">EXCEPT THAT THE CHAIRMAN MAY NOT VOTE FOR CANDIDATES</span> to the Board of Directors), as my attorney-in-fact and proxy, to represent me at the <span class="title-header">ANNUAL GENERAL MEETING</span> of Valley Golf & Country Club, Inc., to be held on 25 September 2022 at 4:00pm , and/or any postponements or adjournment(s) thereof, as fully and to all intents and purposes as I lawfully might or could do if present and voting in person, hereby ratifying and confirming any and all actions taken on matters which may properly come before him during such meeting or adjournment(s) thereof.  In particular, I hereby direct my said proxy to vote on the agenda items as I have expressly indicated by marking with a check “/”the appropriate box below. I also authorize my proxy to vote for any of the candidates for the Board of Directors except the candidates marked with an “X” under the “AGAINST” column:</p>


        <table style="width: 100%">
            <thead>
                <tr>
                    <th rowspan="2" colspan="2">ITEMS</th>
                    <th colspan="3">ACTION</th>
                </tr>
                <tr>
                    <th width="65px">FOR</th>
                    <th width="65px">AGAINST</th>
                    <th width="65px">ABSTAIN</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1.</td>
                    <td>To vote for Quorum purposes only.</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>2.</td>
                    <td>To approve the minutes of the 2021 Annual stockholders’ meeting.</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>3.</td>
                    <td>To approve the Company’s 2022 Annual Report and Audited Financial Statements.</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>4.</td>
                    <td>To confirm and ratify all acts and resolutions of the Board of Directors & Management (July 1, 2021 to June 30, 2022 inclusive).</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>5.</td>
                    <td>To appoint External Auditors.</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>6.</td>
                    <td>To approve the Amendment of Articles II (Secondary Purpose), III (Principal Office Address), IV (Term of Existence), VI (Number of Directors) and VII (On stock certificates) of the Amended Articles of Incorporation</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <p>_______________________________</p>

        <p class="font-calibri"> Stockholders may vote in person online from September 21 (starting at 8:00 a.m.) up to September 22, 2022 (5:00 p.m.). Voting by Proxyholders and attorneys-in-fact online shall be on September 23, 2022 from 8:00 a.m. to 5:00 p.m.</p>
        <p style="text-align: right; margin-top: 180px" class="font-calibri">DATE AND TIME DOWNLOADED: <?php echo EApp::datetime(); ?></p>


        <div class="page-break"></div>
   
        <table style="width: 100%">
            <thead>
                <tr>
                    <th rowspan="2" colspan="2">ITEMS</th>
                    <th colspan="3">ACTION</th>
                </tr>
                <tr>
                    <th width="65px">FOR</th>
                    <th width="65px">AGAINST</th>
                    <th width="65px">ABSTAIN</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>7.</td>
                    <td>To approve the Amendment of Article I (Office), Article III (Meeting: Sections 1 to 10), Article IV (Directors: Section 1 and 3), Article V (Officers: Sections 1 to 5), Article VI (Committees: Section 3) and Article VII (Membership: Section 3) of the Amended By-Laws</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>8.</td>
                    <td>All matters arising from the agenda (except the sale or disposition, total or partial, of the corporate assets).</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
       
                <tr>
                    <td rowspan="6">9.</td>
                    <td>Election of the Board of Directors</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>a.	Marvin A. Caparros</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>b.	Maria Cecilia Ng-Esguerra</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>c.	Raymundo G. Estrada</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>d.	Jose Ferdinand R. Guiang</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>e.	Nicanor S. Jorge</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
               
            </tbody>
        </table>


       
            
        <p>If no instructions are indicated on a returned and duly signed proxy or I left any items in blank, my PROXY may vote the membership certificates under my name on any blank item according to his sound discretion including electing members  of the Board of Directors .By submitting this proxy, I hereby agree that I shall be counted as being present during the annual members’ meeting for quorum purposes notwithstanding my or my proxy’s physical absence during the meeting itself.</p>
        
        <p>I understand that a proxy form that is returned without a signature shall not be valid. I also understand that should I choose to send back the signed proxy form online to the Club’s website, I shall use my registered e-mail address to ensure the integrity of my vote otherwise the proxy shall be considered void.</p>
            
        <p class="title-header semi-title">Voting Rules</p>

        <p>Each shareholder owning one share or his Proxy is entitled to cast one vote for as many positions for directors as are being voted upon or cumulate the votes and cast it in favor one or two candidates. Example –If there are 3 seats for directors open for voting, the shareholder or his proxy is entitled to 3 votes. He/she may either distribute the 3 votes among 3 candidates of his/her choice, cast all 3 votes in favor of one candidate or cast 2 votes in favor of 1 candidate and 1 vote for another candidate. If the shareholder owns more than one share, each share shall be entitled to 3 votes which he/she may distribute evenly or cumulate in favor of just one or two candidates. </p>
        
        <p class="title-header semi-title">Validation of Proxies</p>

        <p>Proxy form shall be validated as these are received by the Club, provided that the proxy forms are submitted to the Club on or before <span class="title-header">5:00 p.m. of September 18, 2022</span> which is consistent with the deadline provided under the Club’s By-Laws.</p>
            
        <p class="title-header semi-title">Revocation of Proxies</p>

        <p>A member giving a proxy has the power to revoke it any time before the right granted is exercised.  A proxy is also considered revoked if the member decides to vote and actually votes online from Sept. 21 to 22, 2022 </p>

        <p style="margin-top: 25px;">Signed this ________________ at ____________________</p>
        
        <p style="text-align: right;" class="font-calibri">DATE AND TIME DOWNLOADED: <?php echo EApp::datetime(); ?></p>

      
        <div class="page-break"></div>
        
        <p style="width: 50%; text-align: center; font-weight: bold; margin-bottom: -25px; font-size: 15px"><?php echo $stockholder; ?></p>
        <p style="margin-top: -40x">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;____________________________________________________  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; _______________________________________________<br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Printed Name of Member &nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;Signature of Member or Authorized Signatory</p>
      
      
         
        <br>
        <p style="text-align: center;"><u>PLEASE DATE AND SIGN YOUR PROXY</u></p>


        <p>PLEASE MARK, SIGN AND EMAIL BACK YOUR PROXY AT VALLEY WEBSITE USING YOUR REGISTERED EMAIL ADDRESS OR SEND IT THRU PRIVATE COURIER ON OR BEFORE 5:00 P.M. OF SEPTEMBER 18, 2022.</p>

        <p>ADDRESS:VALLEY GOLF & COUNTRY CLUB, INC. DON CELSO S. TUASON AVE., ANTIPOLO CITY.</p>
        <br>
        <p class="title-header semi-title" style="text-align: center;">LETTER OF INTENT AND DATA PRIVACY CONSENT</p>
        <p>I <u>&nbsp;&nbsp;<?php echo $stockholder ?>&nbsp;&nbsp;</u>, hereby declare and signify my intent to participate by remote communication and exercise the right to vote in absentia in the September 25, 2022 Annual Stockholders’ Meeting of Valley Golf & Country Club, Inc. </p>

        <p>By participating in the on-line voting and in compliance with Republic Act No. 10173 (or the Data Privacy Act of 2012) and its Implementing Rules and Regulations (IRR) effective since September 8, 2016, I freely and voluntarily authorize Valley Golf & Country Club, Inc. to collect, record, organize, use, disclose  and/or otherwise process any personal information submitted in connection with this declaration only for the purpose of allowing me to participate in the stockholders’ meeting by remote communication and vote in absentia. I understand that by giving this consent, I am not waiving any of my rights other than as authorized herein under the Data Privacy Act of 2012 and other applicable laws.</p><br>

        <p>Signed this  ____ day of ______________  at___________________ City.</p><br><br>



        <p style="margin: 0px; padding: 0px; text-align: center;">_________________________________ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  ________________________________________</p>
        <p style="margin: 0px; padding: 0px; width: 100%;"><span style="width: 100%; margin: 0px 0px 0px 108px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Signature over printed name</span> <span style="width: 100%; margin: 0px 0px 0px 100px;"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Account No.</span></p>




 
        <p style="text-align: right; margin-top: 280px" class="font-calibri">DATE AND TIME DOWNLOADED: <?php echo EApp::datetime(); ?></p>
    </body>
</html>