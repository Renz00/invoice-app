<script setup>
import axios from "axios"
import { onMounted, ref } from "vue"
import {useRouter} from "vue-router"
import router from "../../router";

let form = ref([]) //closed brackets variable is an array
let allcustomers = ref([])
// let customer_id = ref([])
let item = ref([])
let listCart = ref([])
const modalstate = ref(false) //declaring a boolean
let listproducts = ref([])


onMounted(async () => {
    indexForm()
    getAllCustomers()
    getproducts()
})

const indexForm = async () => {
    let response = await axios.get('/api/create_invoice')
    //console.log('form', response.data)
    form.value = response.data
}

const getAllCustomers = async () => {
    let response = await axios.get('/api/customers')
    //console.log('response', response.data)
    allcustomers.value = response.data.customers
}

const addCart = (item) => {
    //item variable is used as param
    //creates an itemcart object and pushes it into listCart array
    const itemcart = {
        id: item.id,
        item_code: item.item_code,
        description: item.description,
        unit_price: item.unit_price,
        quantity: item.quantity
    }
    //sets default value 1 for quantity to avoid computation errors
    item.quantity = 1
    listCart.value.push(itemcart)
    modalstate.value = false
}

const modalToggle = (value) => {
    //recieves a value param with is either true or false
    //modalstate determines whether the modal is shown or hidden
    modalstate.value = value
}

const getproducts = async () => {
    let res = await axios.get('/api/products')
    //console.log('products', res)
    listproducts.value = res.data.products
}

const removeItem = (index) => {
    //removes 1 item from the array using the index
    listCart.value.splice(index, 1)
}

const subTotal = () => {
    //computes subtotal by looping through listCart
    //and adding the products of quantity and unit_price
    //to the total return var
    let total = 0
    listCart.value.map((data)=>{
        total = total + (data.quantity*data.unit_price)
    })
    return parseFloat(total).toFixed(2)
}

const Total = () => { //computes the discounted total
    let pecent_in_decimal = 0
    let discount_val = 0
    let subtotal = subTotal()

    pecent_in_decimal = form.value.discount / 100
    discount_val = subtotal * pecent_in_decimal

    return parseFloat(subtotal - discount_val).toFixed(2)
}

const onSave = () => {
    //checks if there are items in the array for the item cart
    if (listCart.value.length >= 1){
        let subtotal = 0
        subtotal = subTotal()

        let total = 0
        total = Total()

        const formData = new FormData()
        formData.append('invoice_item', JSON.stringify(listCart.value))
        formData.append('customer_id', form.value.customer_id)
        formData.append('date', form.value.date)
        formData.append('due_date', form.value.due_date)
        formData.append('number', form.value.number)
        formData.append('reference', form.value.reference)
        formData.append('discount', form.value.discount)
        formData.append('subtotal', subtotal)
        formData.append('total', total)
        formData.append('terms_and_conditions', form.value.terms_and_conditions)

        axios.post('/api/add_invoice/', formData)
        listCart.value = []
        router.push('/')
    }
}

</script>

<template>
    <div class="invoices">

        <div class="card__header">
            <div>
                <h2 class="invoice__title">New Invoice</h2>
            </div>
            <div>

            </div>
        </div>

        <div class="card__content">
            <div class="card__content--header">
                <div>
                    <p class="my-1">Select Customer</p>
                    <select name="" id="" class="input" v-model="form.customer_id">
                        <option v-for="customer in allcustomers" :key="customer.id" :value="customer.id">
                            {{customer.firstname}}
                        </option>
                    </select>
                </div>
                <div>
                    <p class="my-1">Date</p>
                    <input id="date" placeholder="dd-mm-yyyy" type="date" class="input" v-model="form.date"> <!---->
                    <p class="my-1">Due Date</p>
                    <input id="due_date" type="date" class="input" v-model="form.due_date">
                </div>
                <div>
                    <p class="my-1">Number</p>
                    <input type="text" class="input" v-model="form.number">
                    <p class="my-1">Reference(Optional)</p>
                    <input type="text" class="input" v-model="form.reference">
                </div>
            </div>
            <br><br>
            <div class="table">

                <div class="table--heading2">
                    <p>Item Description</p>
                    <p>Unit Price</p>
                    <p>Qty</p>
                    <p>Total</p>
                    <p></p>
                </div>

                <!-- item 1 -->
                <div class="table--items2" v-for="(itemcart, index) in listCart" :key="itemcart.id">
                    <p>#{{itemcart.item_code}}</p>
                    <p>
                        <input type="number" min="1" class="input" v-model="itemcart.unit_price">
                    </p>
                    <p>
                        <input type="number" min="1" class="input" v-model="itemcart.quantity">
                    </p>
                    <!-- if itemcart.quantity is not empty/null -->
                    <p v-if="itemcart.quantity">
                        $ {{ (itemcart.quantity)*(itemcart.unit_price) }}
                    </p>
                    <p v-else>-</p>
                    <p style="color: red; font-size: 24px;cursor: pointer;" @click="removeItem(index)">
                        &times;
                    </p>
                </div>
                <div style="padding: 10px 30px !important;">
                    <button class="btn btn-sm btn__open--modal" @click="modalToggle(true)">
                        Add New Line</button>
                </div>
            </div>

            <div class="table__footer">
                <div class="document-footer" >
                    <p>Terms and Conditions</p>
                    <textarea cols="50" rows="7" class="textarea" v-model="form.terms_and_conditions"></textarea>
                </div>
                <div>
                    <div class="table__footer--subtotal">
                        <p>Sub Total</p>
                        <span>$ {{ subTotal() }}</span>
                    </div>
                    <div class="table__footer--discount">
                        <p>Discount %</p>
                        <input type="number" min="0" max="100" class="input" v-model="form.discount">
                    </div>
                    <div class="table__footer--total">
                        <p>Grand Total</p>
                        <span>$ {{ Total() }}</span>
                    </div>
                </div>
            </div>


        </div>
        <div class="card__header" style="margin-top: 40px;">
            <div>

            </div>
            <div>
                <a class="btn btn-secondary" @click="onSave()">
                    Save
                </a>
            </div>
        </div>

        <!-- show modal if showModal value is true -->
    <div class="modal main__modal " :class="{ show: modalstate}">
        <div class="modal__content">
            <span class="modal__close btn__close--modal" @click="modalToggle(false)">×</span>
            <h3 class="modal__title">Add Item</h3>
            <hr><br>
            <div class="modal__items">
                <ul style="list-style:none;">
                    <li v-for="(item, index) in listproducts" :key="item.id" style="display:grid; grid-template-columns:30px 350px 15px; align-items:center; padding-bottom:5px;">
                        <p>{{ index+1 }}</p>
                        <a href="#">{{item.item_code}} {{item.description}}</a>
                        <button @click="addCart(item)" style="border: 1px solid #e0e0e0; width: 35px; height: 35px; cursor: pointer;">
                        +
                        </button>
                    </li>
                </ul>
            </div>
            <br><hr>
            <!-- <div class="model__footer">
                <button class="btn btn-light mr-2 btn__close--modal" @click="modalToggle(false)">
                    Cancel
                </button>
                <button class="btn btn-light btn__close--modal ">Save</button>
            </div> -->
        </div>
    </div>

    <br><br><br>
    </div>
</template>
