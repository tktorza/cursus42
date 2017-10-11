/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_arg.c                                           :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <marvin@42.fr>                     +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2016/02/27 14:07:51 by tktorza           #+#    #+#             */
/*   Updated: 2016/03/09 14:29:08 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include "header.h"

static t_chain	*ft_argo_bis(t_chain *chain, va_list *ap)
{
	chain->arg = ft_itoa((va_arg(*ap, int)));
	chain->type = ft_strdup("d");
	return (ft_chain_next(chain));
}

static t_chain	*ft_enchain(int *i, va_list *ap, t_chain *chain, const char *s)
{
	chain->arg = ft_arg(s, i, ap);
	chain->type = ft_type(s, *i);
	chain = ft_chain_next(chain);
	return (chain);
}

void			ft_argo(int i, t_chain *chain, va_list *ap, const char *s)
{
	while (s[i])
	{
		if (s[i] == '%')
		{
			i++;
			while (s[i] && ft_strchr(ft_strdup("#0-+ hljz012345\
							6789."), s[i]) != 0)
				i++;
			if (s[i] == '*')
			{
				chain = ft_argo_bis(chain, ap);
				if (s[i + 1] && s[i + 1] == '.' && s[i + 2] && s[i + 2] == '*')
					chain = ft_argo_bis(chain, ap);
				while (s[i] && ft_strchr(ft_strdup("0123456789.h\
								ljz*"), s[i]) != 0)
					i++;
			}
			if (ft_strchr(ft_strdup("bsdciouxOUXDpCS"), s[i]) != 0)
				chain = ft_enchain(&i, ap, chain, s);
		}
		i++;
	}
}
