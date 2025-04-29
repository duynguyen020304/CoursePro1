<?php include('template/head.php'); ?>
<link href="public/CSS/checkout.css" rel="stylesheet">
<?php include('template/header.php'); ?>

<section class="checkout-section py-5">
    <div class="container">
        <h2 class="text-center mb-4">Thông Tin Thanh Toán</h2>
        <div class="row">
            <div class="col-md-8">
                <div class="checkout-billing-address mb-4">
                    <h4>Địa chỉ Thanh Toán</h4>
                    <div class="form-group">
                        <label for="country">Quốc gia</label>
                        <select class="form-control" id="country" name="country" required>
                            <option value="vietnam">Việt Nam</option>
                            <option value="usa">Hoa Kỳ</option>
                        </select>
                    </div>     
                </div>

                <div class="checkout-payment-method mb-4">
                    <h4>Phương Thức Thanh Toán <small class="text-muted ml-2">An toàn và mã hóa <i class="fas fa-lock"></i></small></h4> 
                    <div class="payment-options">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="payment_method" id="method-card" value="card" checked>
                            <label class="form-check-label" for="method-card">
                                <img src="..." alt="Visa"> <img src="..." alt="Mastercard">
                                <img src="https://via.placeholder.com/150x30?text=Card+Logos" alt="Card Logos" class="ml-2" style="height: 20px;">
                            </label>
                        </div>
                        <div id="card-details" class="ml-4 mt-2"> 
                             <div class="form-group">
                                 <label for="card-number">Số thẻ</label>
                                 <input type="text" class="form-control" id="card-number" name="card_number" placeholder="xxxx xxxx xxxx xxxx" required>
                             </div>
                             <div class="form-row">
                                 <div class="col-md-6 form-group">
                                     <label for="expiry-date">Ngày hết hạn (MM/YY)</label>
                                     <input type="text" class="form-control" id="expiry-date" name="expiry_date" placeholder="MM/YY" required>
                                 </div>
                                  <div class="col-md-6 form-group">
                                     <label for="cvc-cvv">CVC/CVV</label>
                                     <input type="text" class="form-control" id="cvc-cvv" name="cvc_cvv" required>
                                 </div>
                             </div>
                             <div class="form-group">
                                <label for="name-on-card">Tên trên thẻ</label>
                                <input type="text" class="form-control" id="name-on-card" name="name_on_card" required>
                             </div>
                             <div class="form-group form-check">
                                 <input type="checkbox" class="form-check-input" id="save-card" name="save_card">
                                 <label class="form-check-label" for="save-card">Lưu thẻ này cho lần mua sau</label>
                             </div>
                        </div>

                        <div class="form-check mt-2"> 
                            <input class="form-check-input" type="radio" name="payment_method" id="method-applepay" value="applepay">
                            <label class="form-check-label" for="method-applepay">
                                Apple Pay
                            </label>
                        </div>

                        <div class="form-check mt-2"> 
                            <input class="form-check-input" type="radio" name="payment_method" id="method-googlepay" value="googlepay">
                            <label class="form-check-label" for="method-googlepay">
                                Google Pay
                            </label>
                        </div>

                        <div class="form-check mt-2"> 
                            <input class="form-check-input" type="radio" name="payment_method" id="method-paypal" value="paypal">
                            <label class="form-check-label" for="method-paypal">
                                PayPal 
                            </label>
                        </div>
                    </div>
                        <button class="btn btn-dark btn-block mt-3" id="gpay-button" style="display: none;">G Pay</button>
                </div>

                <div class="checkout-order-details">
                    <h4>Chi Tiết Đơn Hàng</h4>

                    <div class="order-detail-item border-bottom py-2"> 
                         <div class="row align-items-center"> 
                             <div class="col-3">
                                 <img src="https://via.placeholder.com/80x50?text=Course+Image" alt="Course Image" class="img-fluid"> 
                             </div>
                             <div class="col-6">
                                <p class="mb-0">Lập trình Web</p> 
                                <small class="text-muted">Giảng viên: John Doe</small>
                             </div>
                              <div class="col-3 text-right">
                                 <p class="mb-0 font-weight-bold">799.000 VNĐ</p>
                             </div>
                         </div>
                    </div>

                     <div class="order-detail-item border-bottom py-2">
                         <div class="row align-items-center">
                             <div class="col-3">
                                 <img src="https://via.placeholder.com/80x50?text=Course+Image" alt="Course Image" class="img-fluid">
                             </div>
                             <div class="col-6">
                                 <p class="mb-0">Thiết kế Đồ họa</p>
                                <small class="text-muted">Giảng viên: Jane Smith</small>
                             </div>
                              <div class="col-3 text-right">
                                 <p class="mb-0 font-weight-bold">650.000 VNĐ</p>
                             </div>
                         </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="checkout-summary">
                    <h4>Tóm Tắt Đơn Hàng</h4>
                    <div class="summary-item">
                         <p>Giá gốc:</p>
                         <p>1.449.000 VNĐ</p>
                    </div>
                    <div class="summary-item total">
                         <h5>Tổng Cộng:</h5>
                         <h5><span id="total-price">1.449.000 VNĐ</span></h5>
                    </div>

                     <form action="process_checkout.php" method="POST"> 
                          <input type="hidden" name="total_amount" value="1449000">
                         <button type="submit" class="btn btn-success btn-lg btn-block mt-3">Thanh Toán</button>
                     </form>
                </div>
            </div>
        </div>
    </div>
</section>
