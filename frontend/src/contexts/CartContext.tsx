// Cart Context with TypeScript
// Provides shopping cart state and operations

import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useState,
  type ReactNode,
} from 'react';
import { cartApi } from '../services/api';
import type { Cart } from '../schemas/cart/apiResponses.schema';
import { useAuth } from './AuthContext';

/**
 * Cart context state
 */
interface CartState {
  cart: Cart | null;
  loading: boolean;
  initialized: boolean;
  items: Cart['items'] | [];
  itemCount: number;
}

/**
 * Cart context value shape with all methods
 */
interface CartContextValue extends CartState {
  fetchCart: () => Promise<void>;
  addItem: (courseId: string, quantity?: number) => Promise<{ success: boolean; data?: unknown; message?: string }>;
  removeItem: (cartItemId: string) => Promise<{ success: boolean; message?: string }>;
  clearCart: () => Promise<{ success: boolean; message?: string }>;
}

/**
 * Cart context with nullable initial value
 */
const CartContext = createContext<CartContextValue | null>(null);

/**
 * Props for CartProvider
 */
interface CartProviderProps {
  children: ReactNode;
}

/**
 * Cart Provider component
 * Manages shopping cart state including items, loading, and operations
 */
export function CartProvider({ children }: CartProviderProps) {
  const [cart, setCart] = useState<Cart | null>(null);
  const [loading, setLoading] = useState(false);
  const [initialized, setInitialized] = useState(false);
  const { isAuthenticated, loading: authLoading } = useAuth();

  /**
   * Fetches the current cart from the API
   */
  const fetchCart = useCallback(async (): Promise<void> => {
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
      const errorResponse = error as { response?: { status?: number } };
      if (errorResponse.response?.status !== 401) {
        console.error('Failed to fetch cart:', error);
      }
    } finally {
      setLoading(false);
      setInitialized(true);
    }
  }, [isAuthenticated]);

  // Fetch cart when auth state changes
  useEffect(() => {
    if (authLoading) {
      return;
    }

    fetchCart();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [authLoading, isAuthenticated]);

  /**
   * Adds an item to the cart
   */
  const addItem = useCallback(
    async (courseId: string, quantity = 1): Promise<{ success: boolean; data?: unknown; message?: string }> => {
      try {
        const response = await cartApi.addItem(courseId, quantity);
        await fetchCart();
        return { success: true, data: response };
      } catch (error) {
        const errorResponse = error as { response?: { data?: { message?: string } } };
        return {
          success: false,
          message: errorResponse.response?.data?.message || 'Failed to add to cart',
        };
      }
    },
    [fetchCart]
  );

  /**
   * Removes an item from the cart
   */
  const removeItem = useCallback(
    async (cartItemId: string): Promise<{ success: boolean; message?: string }> => {
      try {
        await cartApi.removeItem(cartItemId);
        await fetchCart();
        return { success: true };
      } catch (error) {
        const errorResponse = error as { response?: { data?: { message?: string } } };
        return {
          success: false,
          message: errorResponse.response?.data?.message || 'Failed to remove from cart',
        };
      }
    },
    [fetchCart]
  );

  /**
   * Clears all items from the cart
   */
  const clearCart = useCallback(async (): Promise<{ success: boolean; message?: string }> => {
    try {
      await cartApi.clear();
      setCart(null);
      return { success: true };
    } catch (error) {
      const errorResponse = error as { response?: { data?: { message?: string } } };
      return {
        success: false,
        message: errorResponse.response?.data?.message || 'Failed to clear cart',
      };
    }
  }, []);

  const value: CartContextValue = {
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

/**
 * Hook to access cart context
 * @throws Error if used outside of CartProvider
 */
export function useCart(): CartContextValue {
  const context = useContext(CartContext);
  if (!context) {
    throw new Error('useCart must be used within a CartProvider');
  }
  return context;
}
