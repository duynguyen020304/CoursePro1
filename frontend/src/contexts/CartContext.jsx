/* eslint-disable react-refresh/only-export-components */
import { createContext, useContext, useEffect, useState } from 'react';
import { cartApi } from '../services/api';
import { useAuth } from './AuthContext';

const CartContext = createContext(null);

export function CartProvider({ children }) {
  const [cart, setCart] = useState(null);
  const [loading, setLoading] = useState(false);
  const [initialized, setInitialized] = useState(false);
  const { isAuthenticated, loading: authLoading } = useAuth();

  const fetchCart = async () => {
    if (!isAuthenticated) {
      setCart(null);
      setInitialized(true);
      return;
    }

    try {
      setLoading(true);
      const response = await cartApi.get();
      setCart(response.data.data);
    } catch (error) {
      if (error.response?.status !== 401) {
        console.error('Failed to fetch cart:', error);
      }
    } finally {
      setLoading(false);
      setInitialized(true);
    }
  };

  const addItem = async (course_id) => {
    try {
      const response = await cartApi.addItem(course_id);
      await fetchCart();
      return { success: true, data: response.data.data };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Failed to add to cart',
      };
    }
  };

  const removeItem = async (cartItemId) => {
    try {
      await cartApi.removeItem(cartItemId);
      await fetchCart();
      return { success: true };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Failed to remove from cart',
      };
    }
  };

  const clearCart = async () => {
    try {
      await cartApi.clear();
      setCart(null);
      return { success: true };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Failed to clear cart',
      };
    }
  };

  useEffect(() => {
    if (authLoading) {
      return;
    }

    fetchCart();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [authLoading, isAuthenticated]);

  const value = {
    cart,
    loading,
    initialized,
    items: cart?.items || [],
    itemCount: cart?.items?.length || 0,
    fetchCart,
    addItem,
    removeItem,
    clearCart,
  };

  return <CartContext.Provider value={value}>{children}</CartContext.Provider>;
}

export function useCart() {
  const context = useContext(CartContext);
  if (!context) {
    throw new Error('useCart must be used within a CartProvider');
  }
  return context;
}
