<?php
if (!defined('SITE_URL')) {
    // Fallback, should be defined in config.php
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $subdirectory = ''; // Define or load your subdirectory if applicable
    define('SITE_URL', rtrim($protocol . $host . $subdirectory, '/'));
}
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(__DIR__));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy | AffiliateBasic System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>

<body>
    <?php include PROJECT_ROOT_PATH . '/templates/navbar.php'; ?>

    <main class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h1 class="display-4 fw-bold mb-4">Privacy Policy for AffiliateBasic System</h1>
                    <p class="lead">Last updated: <?php echo date("F j, Y"); ?>
                    </p>

                    <p>Welcome to AffiliateBasic System ("us", "we", or "our"). We are committed to protecting your
                        personal
                        information and your right to privacy. If you have any questions or concerns about this privacy
                        notice, or our practices with regards to your personal information, please contact us via the <a
                            href="<?php echo SITE_URL; ?>/contact">contact page</a>.</p>
                    <p>This privacy notice describes how we might use your information if you:
                    <ul>
                        <li>Visit our website at [Your Website URL, e.g., <?php echo SITE_URL; ?>]</li>
                        <li>Register an account with us</li>
                        <li>Participate in our Affiliate Program</li>
                        <li>Engage with us in other related ways, including any sales, marketing, or events</li>
                    </ul>
                    In this privacy notice, if we refer to "Website", "System", or "Service", we are referring to
                    AffiliateBasic System and its functionalities.
                    </p>
                    <p>The purpose of this privacy notice is to explain to you in the clearest way possible what
                        information we collect, how we use it, and what rights you have in relation to it. If there are
                        any terms in this privacy notice that you do not agree with, please discontinue use of our
                        Services immediately.</p>

                    <hr class="my-4">

                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h2 class="h4 card-title"><i class="bi bi-journal-text text-primary me-2"></i>1. What
                                Information We Collect</h2>
                            <p>We collect personal information that you voluntarily provide to us when you register on
                                the Website, express an interest in obtaining information about us or our products and
                                Services, when you participate in activities on the Website (such as making a purchase
                                or participating in our affiliate program) or otherwise when you contact us.</p>
                            <p>The personal information that we collect depends on the context of your interactions with
                                us and the Website, the choices you make, and the products and features you use. The
                                personal information we collect may include the following:</p>
                            <ul>
                                <li><strong>Personal Identification Information:</strong> Username, email address.</li>
                                <li><strong>Account Credentials:</strong> Hashed passwords and security information used
                                    for authentication and account access.</li>
                                <li><strong>Order Information:</strong> If you make a purchase, we collect information
                                    related to your order, which may include product details, quantities, total amount,
                                    shipping address, billing address (if different), and selected payment method.
                                    Please note: we do **not** directly collect or store full payment card details.
                                    Payment processing is handled by third-party payment gateways (if implemented -
                                    currently simulated).</li>
                                <li><strong>Contact Form Submissions:</strong> Name, email address, subject, and message
                                    content when you use our contact form.</li>
                                <li><strong>Affiliate Program Information:</strong> If you participate in our affiliate
                                    program, we collect your unique affiliate code, track orders referred by you,
                                    calculate earned commissions, maintain your affiliate balance, and collect payment
                                    details you provide for withdrawals (e.g., PayPal email).</li>
                            </ul>
                            <p><strong>Information automatically collected:</strong></p>
                            <p>We automatically collect certain information when you visit, use, or navigate the
                                Website. This information does not reveal your specific identity (like your name or
                                contact information) but may include device and usage information, such as your IP
                                address, browser and device characteristics, operating system, language preferences,
                                referring URLs (including affiliate referral codes), device name, country, location,
                                information about how and when you use
                                our Website, and other technical information. This information is primarily needed to
                                maintain the security and operation of our Website, and for our internal analytics and
                                reporting purposes.</p>
                            <ul>
                                <li>Log and Usage Data (login timestamps, pages viewed, IP addresses, referral data).
                                </li>
                                <li>Device Data.</li>
                                <li>Cookies and Similar Technologies (Session cookies for referral tracking; you should
                                    detail your cookie usage here or link
                                    to a separate Cookie Policy).</li>
                            </ul>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h2 class="h4 card-title"><i class="bi bi-gear-fill text-primary me-2"></i>2. How We Use
                                Your Information</h2>
                            <p>We use personal information collected via our Website for a variety of business purposes
                                described below. We process your personal information for these purposes in reliance on
                                our legitimate business interests, in order to enter into or perform a contract with
                                you, with your consent, and/or for compliance with our legal obligations.</p>
                            <ul>
                                <li><strong>To facilitate account creation and logon process.</strong></li>
                                <li><strong>To manage user accounts.</strong> We may use your information for the
                                    purposes of managing your account and keeping it in working order.</li>
                                <li><strong>To fulfill and manage your orders.</strong> We may use your information to
                                    fulfill and manage your orders, payments (simulated), returns, and exchanges made
                                    through the Website.</li>
                                <li><strong>To manage our Affiliate Program.</strong> If you are an affiliate, we use
                                    your information to track referrals, calculate and attribute earnings, process
                                    withdrawal requests, and communicate with you regarding the affiliate program. If a
                                    customer makes a purchase using an affiliate referral code, we process data related
                                    to that transaction to credit the respective affiliate.</li>
                                <li><strong>To send administrative information to you.</strong> We may use your personal
                                    information to send you product, service, and new feature information and/or
                                    information about changes to our terms, conditions, and policies.</li>
                                <li><strong>To protect our Services.</strong> We may use your information as part of our
                                    efforts to keep our Website safe and secure (for example, for fraud monitoring and
                                    prevention).</li>
                                <li><strong>To respond to user inquiries/offer support to users.</strong> We may use
                                    your information to respond to your inquiries and solve any potential issues you
                                    might have with the use of our Services (e.g., via contact form submissions).</li>
                                <li><strong>For other Business Purposes,</strong> such as data analysis, identifying
                                    usage trends, determining the effectiveness of our promotional campaigns, and to
                                    evaluate and improve our Website, products, marketing, and your experience.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h2 class="h4 card-title"><i class="bi bi-share-fill text-primary me-2"></i>3. Will Your
                                Information Be Shared With Anyone?</h2>
                            <p>We only share information with your consent, to comply with laws, to provide you with
                                services, to protect your rights, or to fulfill business obligations.</p>
                            <p>We may process or share your data that we hold based on the following legal basis:</p>
                            <ul>
                                <li><strong>Consent:</strong> We may process your data if you have given us specific
                                    consent to use your personal information for a specific purpose.</li>
                                <li><strong>Legitimate Interests:</strong> We may process your data when it is
                                    reasonably necessary to achieve our legitimate business interests (e.g., managing
                                    the affiliate program).</li>
                                <li><strong>Performance of a Contract:</strong> Where we have entered into a contract
                                    with you (e.g., for a purchase, or affiliate agreement), we may process your
                                    personal information to fulfill
                                    the terms of our contract.</li>
                                <li><strong>Legal Obligations:</strong> We may disclose your information where we are
                                    legally required to do so in order to comply with applicable law, governmental
                                    requests, a judicial proceeding, court order, or legal process.</li>
                                <li><strong>Vital Interests:</strong> We may disclose your information where we believe
                                    it is necessary to investigate, prevent, or take action regarding potential
                                    violations of our policies, suspected fraud, situations involving potential threats
                                    to the safety of any person and illegal activities, or as evidence in litigation in
                                    which we are involved.</li>
                            </ul>
                            <p>More specifically, we may need to process your data or share your personal information in
                                the following situations:</p>
                            <ul>
                                <li><strong>Business Transfers.</strong> We may share or transfer your information in
                                    connection with, or during negotiations of, any merger, sale of company assets,
                                    financing, or acquisition of all or a portion of our business to another company.
                                </li>
                                <li><strong>Third-Party Service Providers.</strong> We may share your data with
                                    third-party vendors, service providers, contractors, or agents who perform services
                                    for us or on our behalf and require access to such information to do that work.
                                    Examples include: payment processing (if implemented for affiliate payouts), data
                                    analysis, email delivery, hosting services, customer
                                    service, and marketing efforts. We will ensure that any third parties we use are
                                    contractually bound to keep your information confidential and use it only for the
                                    purposes for which we disclose it to them.</li>
                                <li><strong>Affiliate Program Operations:</strong> If you make a purchase using an
                                    affiliate referral code, limited information about the order (e.g., order ID, order
                                    total, product purchased for commission calculation, date) may be made available to
                                    the referring affiliate to track their earnings. Your personal identification beyond
                                    what is necessary for commission tracking will not be shared with the affiliate
                                    without your consent. Affiliates participating in the program will have access to
                                    their own earning reports and referral statistics.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h2 class="h4 card-title"><i class="bi bi-shield-lock-fill text-primary me-2"></i>4. How We
                                Keep Your Information Safe</h2>
                            <p>We have implemented appropriate technical and organizational security measures designed
                                to protect the security of any personal information we process. These include:</p>
                            <ul>
                                <li>Password hashing using bcrypt for user credentials.</li>
                                <li>CSRF (Cross-Site Request Forgery) protection on forms.</li>
                                <li>Use of prepared statements (PDO) to prevent SQL injection.</li>
                                <li>Secure session management practices.</li>
                                <li>Limiting access to personal data to authorized personnel only.</li>
                                <li>Regularly reviewing our information collection, storage, and processing practices,
                                    including physical security measures, to guard against unauthorized access to
                                    systems.</li>
                            </ul>
                            <p>However, despite our safeguards and efforts to secure your information, no electronic
                                transmission over the Internet or information storage technology can be guaranteed to be
                                100% secure. Therefore, we cannot promise or guarantee that hackers, cybercriminals, or
                                other unauthorized third parties will not be able to defeat our security and improperly
                                collect, access, steal, or modify your information. Although we will do our best to
                                protect your personal information, transmission of personal information to and from our
                                Website is at your own risk. You should only access the Website within a secure
                                environment.</p>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h2 class="h4 card-title"><i class="bi bi-clock-history text-primary me-2"></i>5. How Long
                                We Keep Your Information</h2>
                            <p>We will only keep your personal information for as long as it is necessary for the
                                purposes set out in this privacy notice, unless a longer retention period is required or
                                permitted by law (such as tax, accounting, or other legal requirements).</p>
                            <p>For example, user account information is kept as long as the account is active. Order
                                information and affiliate earning records may be kept for a longer period for financial,
                                tax, and audit trail purposes. When
                                we have no ongoing legitimate business need to process your personal information, we
                                will either delete or anonymize such information, or, if this is not possible (for
                                example, because your personal information has been stored in backup archives), then we
                                will securely store your personal information and isolate it from any further processing
                                until deletion is possible.</p>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h2 class="h4 card-title"><i class="bi bi-person-lines-fill text-primary me-2"></i>6. Your
                                Privacy Rights</h2>
                            <p>Depending on your location, you may have certain rights regarding your personal
                                information under applicable data protection laws. These may include the right to (i)
                                request access and obtain a copy of your personal information, (ii) request
                                rectification or erasure; (iii) to restrict the processing of your personal information;
                                and (iv) if applicable, to data portability. In certain circumstances, you may also have
                                the right to object to the processing of your personal information.</p>
                            <p>To make such a request, please use the contact details provided below. We will consider
                                and act upon any request in accordance with applicable data protection laws.</p>
                            <p>If you have an account with us, you can review and change your personal information by
                                logging into your account and visiting your profile page, and if applicable, your
                                affiliate dashboard.</p>
                            <p>You may also unsubscribe from our marketing email list at any time by clicking on the
                                unsubscribe link in the emails that we send or by contacting us using the details
                                provided below. You will then be removed from the marketing email list – however, we may
                                still communicate with you, for example to send you service-related emails that are
                                necessary for the administration and use of your account (e.g., order confirmations,
                                password resets, affiliate program updates).</p>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h2 class="h4 card-title"><i class="bi bi-cookie text-primary me-2"></i>7. Cookies and
                                Tracking Technologies</h2>
                            <p>We may use cookies and similar tracking technologies (like web beacons and pixels) to
                                access or store information. Specific information about how we use such technologies and
                                how you can refuse certain cookies is set out in our Cookie Policy (if you have one,
                                link it here; otherwise, provide details).</p>
                            <p>For AffiliateBasic System, cookies are primarily used for:</p>
                            <ul>
                                <li>Session management to keep you logged in.</li>
                                <li>"Remember Me" functionality if you choose it.</li>
                                <li>Basic site functionality and security (e.g., CSRF tokens stored in session).</li>
                                <li>Affiliate referral tracking (e.g., storing a referral code in a session or
                                    short-term cookie to attribute sales).</li>
                            </ul>
                            <p>(If you implement analytics like Google Analytics, or advertising, you MUST disclose that
                                here and how users can opt-out.)</p>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h2 class="h4 card-title"><i class="bi bi-file-earmark-text-fill text-primary me-2"></i>8.
                                Updates to This Notice</h2>
                            <p>We may update this privacy notice from time to time. The updated version will be
                                indicated by an updated "Last updated" date and the updated version will be effective as
                                soon as it is accessible. If we make material changes to this privacy notice, we may
                                notify you either by prominently posting a notice of such changes or by directly sending
                                you a notification. We encourage you to review this privacy notice frequently to be
                                informed of how we are protecting your information.</p>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h2 class="h4 card-title"><i class="bi bi-envelope-paper-fill text-primary me-2"></i>9.
                                Contact Us About This Notice</h2>
                            <p>If you have questions or comments about this notice, you may contact us via the <a
                                    href="<?php echo SITE_URL; ?>/contact">contact page</a> or by mail at:</p>
                            <p>
                                AffiliateBasic System [or Your Company Name]<br>
                                [Your Physical Address Line 1]<br>
                                [Your Physical Address Line 2, City, Postal Code]<br>
                                [Country]
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include PROJECT_ROOT_PATH . '/templates/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/script.js"></script>
</body>

</html>