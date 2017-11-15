/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   ft_strtrim.c                                       :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: tktorza <tktorza@student.42.fr>            +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2015/11/29 14:15:41 by tktorza           #+#    #+#             */
/*   Updated: 2017/10/18 14:06:26 by tktorza          ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

#include <stdlib.h>
#include "../inc/libft.h"

char	*ft_strtrim(char const *s)
{
	char	*s2;
	int		i;
	int		k;
	int		m;

	i = 0;
	m = 0;
	if (!s)
		return (NULL);
	k = ft_strlen(s) - 1;
	while (s[i] == ' ' || s[i] == '\t' || s[i] == '\n')
		i++;
	while ((s[k] == ' ' && k > i) || (s[k] == '\t' && k > i) ||
			(s[k] == '\n' && k > i))
		k--;
	s2 = malloc(sizeof(char) * (k - i + 2));
	if (s2 == NULL)
		return (NULL);
	s2[++k] = '\0';
	while (i < k)
		s2[m++] = s[i++];
	s2[m] = '\0';
	return (s2);
}
